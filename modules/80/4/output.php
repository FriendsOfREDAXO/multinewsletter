<?php
if(!function_exists('sendActivationMail')) {
	/**
	 * Send activation mail
	 * @param string[] $yform YForm data
	 */
	function sendActivationMail($yform) {
		if(isset($yform->params['values'])) {
			$fields = [];
			foreach($yform->params['values'] as $value) {
				if($value->name != "") {
					$fields[$value->name] = $value->value;
				}
			}
			
			$addon = rex_addon::get('multinewsletter');
			$user = MultinewsletterUser::initByMail($fields['email']);
			if($addon->hasConfig('sender')) {
				$user->sendActivationMail(
					$addon->getConfig('sender'),
					$addon->getConfig("lang_". rex_clang::getCurrentId() ."_sendername"),
					$addon->getConfig("lang_". rex_clang::getCurrentId() ."_confirmsubject"),
					$addon->getConfig("lang_". rex_clang::getCurrentId() ."_confirmcontent")
				);
				// Save to replace "," in group_ids list with pipes
				$user->save();
			}
		}
	}
}

// Deactivate emailobfuscator for POST od GET mail address
if (rex_addon::get('emailobfuscator')->isAvailable()) {
	if(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) != "") {
		emailobfuscator::whitelistEmail(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
	}
	else if (filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL) != "") {
		emailobfuscator::whitelistEmail(filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL));
	}
}

$cols_sm = "REX_VALUE[20]";
if($cols_sm == "") {
	$cols_sm = 12;
}
$cols_md = "REX_VALUE[19]";
if($cols_md == "") {
	$cols_md = 12;
}
$cols_lg = "REX_VALUE[18]";
if($cols_lg == "") {
	$cols_lg = 8;
}
$offset_lg_cols = intval("REX_VALUE[17]");
$offset_lg = "";
if($offset_lg_cols > 0) { /** @phpstan-ignore-line */
	$offset_lg = " mr-lg-auto ml-lg-auto ";
}
	
print '<div class="col-12 col-sm-'. $cols_sm .' col-md-'. $cols_md .' col-lg-'. $cols_lg . $offset_lg .' yform">';

$addon = rex_addon::get('multinewsletter');

if(strlen(filter_input(INPUT_GET, 'activationkey')) > 5 && filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL) != "") {
	// Handle activation key
	$user = MultinewsletterUser::initByMail(filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL));
	if($user->activationkey == filter_input(INPUT_GET, 'activationkey')) {
		print '<p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_confirmation_successful", "") .'</p>';
		$user->activate();
	}
	else if($user->activationkey == 0) {
		print '<p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_already_confirmed", "") .'</p>';
	}
	else {
		print '<p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_invalid_key", "") .'</p>';
	}
}
else {
	$ask_name = "REX_VALUE[2]" == 'true' ? true : false;
	
	// Show form
	$form_data = 'hidden|subscriptiontype|web
			hidden|status|0
			hidden|clang_id|'. rex_clang::getCurrentId() .'
			datestamp|createdate|createdate|mysql
			ip|createip
			action|copy_value|createdate|updatedate
			action|copy_value|createip|updateip
			generate_key|activationkey

			html||<p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_action") .'<br><br></p>'. PHP_EOL;
	if($ask_name) {
		$form_data .= 'choice|title|'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_anrede", "") .'|'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_title_0", "").'=0,'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_title_1", "").'=1|1|0|
			text|grad|'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_grad", "") .'
			text|firstname|'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_firstname", "") .' *|||{"required":"required"}
			text|lastname|'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_lastname", "") .' *|||{"required":"required"}'. PHP_EOL;
	}
	$form_data .= 'text|email|'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_email", "") .' *|||{"required":"required"}
			html||<br><br>'. PHP_EOL;
	// Groups to be displayed
	$group_ids = (array) rex_var::toArray("REX_VALUE[1]");
	if(count($group_ids) == 1) {
		foreach($group_ids as $group_id) {
			$form_data .= 'hidden|group_ids|'. $group_id . PHP_EOL;
		}
	} else if(count($group_ids) > 1) {
		$group_options = [];
		foreach($group_ids as $group_id) {
			$group = new MultinewsletterGroup($group_id);
			$group_options[] = $group->name .'='. $group_id;
		}
		$form_data .= 'choice|group_ids|'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_select_newsletter", "") .'|'. implode(',', $group_options) .'|1|1|
			html||<br><br>'. PHP_EOL;
	}
	
	$form_data .= 'checkbox|privacy_policy_accepted|'. preg_replace( "#\R+#", "<br>", $addon->getConfig("lang_". rex_clang::getCurrentId() ."_privacy_policy", "")) .' *<br><br>|0,1|0|{"required":"required"}
			php|validate_timer|Spamprotection|<input name="validate_timer" type="hidden" value="'. microtime(true) .'" />|

			html||<p>* '. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_compulsory", "") .'<br><br></p>
			html||<p> '. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_safety", "") .'<br><br></p>

			submit|submit|'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_subscribe", "Send") .'|no_db'. PHP_EOL;
	if($ask_name) {
		$form_data .= 'validate|empty|firstname|'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_invalid_firstname", "") .'
			validate|empty|lastname|'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_invalid_name", "") . PHP_EOL;
	}
	$form_data .= 'validate|empty|email|'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_invalid_email", "") .'
			validate|type|email|email|'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_invalid_email", "") .'
			validate|unique|email|'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_already_subscribed", "") .'|rex_375_user
			action|db|rex_375_user
			action|callback|sendActivationMail';

	$yform = new rex_yform;
	$yform->setFormData(trim($form_data));
	$yform->setObjectparams("form_action", rex_getUrl(rex_article::getCurrentId(), rex_clang::getCurrentId()));
	$yform->setObjectparams("Error-occured", $addon->getConfig("lang_". rex_clang::getCurrentId() ."_no_userdata", ""));
	$yform->setObjectparams("real_field_names", true);

	// action - showtext
	$yform->setActionField("showtext", [$addon->getConfig("lang_". rex_clang::getCurrentId() ."_confirmation_sent", "")]);

	echo $yform->getForm();
}

print "</div>";