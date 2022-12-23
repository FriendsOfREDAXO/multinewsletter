<div class="col-12 col-lg-8 yform">
<?php
// Anzuzeigende Gruppen IDs
$group_ids = (array) rex_var::toArray("REX_VALUE[1]");

$addon = rex_addon::get('multinewsletter');

$showform = true;

// Deactivate emailobfuscator for POST od GET mail address
if (rex_addon::get('emailobfuscator')->isAvailable()) {
	if(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) != "") {
		emailobfuscator::whitelistEmail(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
	}
	else if (filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL) != "") {
		emailobfuscator::whitelistEmail(filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL));
	}
}

if(filter_input(INPUT_GET, 'activationkey', FILTER_VALIDATE_INT, ['options' => ['default'=> 0]]) > 0 && filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL) != "") {
	$user = MultinewsletterUser::initByMail(filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL));
	if($user->activationkey == filter_input(INPUT_GET, 'activationkey', FILTER_VALIDATE_INT)) {
		print '<p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_confirmation_successful") .'</p>';
		$user->activate();
	}
	else if($user->activationkey == 0) {
		print '<p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_already_confirmed") .'</p>';
	}
	else {
		print '<p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_invalid_key") .'</p>';
	}
	$showform = false;		
}

$form_groups = filter_input_array(INPUT_POST, ['groups'=> ['filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY]]);

$messages = [];

if(filter_input(INPUT_POST, 'submit') != "") {
	$save = true;
	// Fehlermeldungen finden
	if(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) == "") {
		$messages[] = $addon->getConfig("lang_". rex_clang::getCurrentId() ."_invalid_email");
	}
	
	// Userobjekt deklarieren
	$user = false;
	if(count($messages) > 0) {
		print '<p><b>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_no_userdata") .'</b></p>';
		print '<ul>';
		foreach($messages as $message) {
			print '<li><b>'. $message .'</b></li>';
		}
		print '</ul>';
		print '<br>';
		
		$save = false;
	}
	else if(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) != "") {
		// Ist Benutzer schon in der Newslettergruppe?
		$user = MultinewsletterUser::initByMail(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
		if($user !== false && $user->id > 0 && $user->status == 1) {
			$not_already_subscribed = [];
			if(count($user->group_ids) > 0 && count($form_groups['groups']) > 0) {
				foreach($form_groups['groups'] as $group_id) {
					if(!in_array($group_id, $user->group_ids)) {
						$not_already_subscribed[] = $group_id;
					}
				}
			}
			if(count($form_groups['groups']) > 0 && empty($not_already_subscribed) && $user->privacy_policy_accepted == 1) {
				print '<p><b>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_already_subscribed") .'</b></p>';
				$save = false;
			}

			$showform = false;
		}
	}
	
	if($save) {
		// Benutzer speichern
		if($user !== false) {
			$user->clang_id = rex_clang::getCurrentId();
		}
		else {
			$user = MultinewsletterUser::factory(
				filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
				"",
				"",
				"",
				"",
				rex_clang::getCurrentId()
			);
		}
		$user->createdate = date('Y-m-d H:i:s');
		$user->createip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
		$user->group_ids = $form_groups['groups'];
		$user->status = 0;
		$user->subscriptiontype = 'web';
		$user->activationkey = rand(100000, 999999);
		$user->privacy_policy_accepted = filter_input(INPUT_POST, 'privacy_policy', FILTER_VALIDATE_INT) == 1 ? 1 : 0;
		$user->save();

		// Aktivierungsmail senden und Hinweis ausgeben
		$user->sendActivationMail(
			$addon->getConfig('sender'),
			$addon->getConfig("lang_". rex_clang::getCurrentId() ."_sendername"),
			$addon->getConfig("lang_". rex_clang::getCurrentId() ."_confirmsubject"),
			$addon->getConfig("lang_". rex_clang::getCurrentId() ."_confirmcontent")
		);
		print '<p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_confirmation_sent") .'</p>';
		
		$showform = false;
	}
}


if($showform) {
	if(count($messages) == 0) {
		print '<p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_action") .'</p>';	
	}
?>
	<form action="<?php print rex_getUrl(rex_article::getCurrentId(), rex_clang::getCurrentId()); ?>" method="post" name="subscribe" class="rex-yform">
		<div class="form-group yform-element" id="yform-formular-email">
			<label class="control-label" for="email"><?php print $addon->getConfig("lang_". rex_clang::getCurrentId() ."_email"); ?> *</label>
			<input class="form-control" name="email" id="email" value="<?php print filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) != "" ? filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) : filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL); ?>" type="email" maxlength="100" required>
		</div>
		<?php
			if(count($group_ids) == 1) {
				foreach($group_ids as $group_id) {
					print '<input type="hidden" name="groups['. $group_id.']" value="'. $group_id .'" />';
				}
			}
			else if(count($group_ids) > 1) {
				print '<br clear="all"><p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_select_newsletter") .'</p>';
				
				foreach($group_ids as $group_id) {
					$group = new MultinewsletterGroup($group_id);
					if($group->name != "") {
						print '<div class="form-group yform-element" id="yform-formular">';
						$checked = "";
						if(isset($form_groups[$group_id]) && $form_groups[$group_id] > 0) {
							$checked = ' checked="checked"';
						}
						print '<input class="checkbox" name="groups['. $group_id .']" id="yform-formular-'. $group_id .'" value="'. $group_id .'" type="checkbox"'. $checked .'>';
						print '<label class="control-label" for="groups['. $group_id .']">'. $group->name .'</label>';
						print '</div>';
					}
				}
			}
			
			// Privacy policy
			print '<div class="form-group yform-element" id="yform-formular">';
			print '<label class="control-label" for="privacy_policy">';
			print '<input class="checkbox" name="privacy_policy" id="yform-formular-privacy-policy" value="1" type="checkbox"'. (filter_input(INPUT_POST, 'privacy_policy', FILTER_VALIDATE_INT) == 1 ? ' checked="checked"' : '') .' required>';
			print $addon->getConfig("lang_". rex_clang::getCurrentId() ."_privacy_policy") .' *</label>';
			print '</div>';
		?>
		<p><?php print $addon->getConfig("lang_". rex_clang::getCurrentId() ."_compulsory"); ?></p>
		<p><?php print $addon->getConfig("lang_". rex_clang::getCurrentId() ."_safety"); ?></p>
		<div class="form-group yform-element">
			<input class="btn btn-primary" name="submit" id="submit" value="<?php print $addon->getConfig("lang_". rex_clang::getCurrentId() ."_subscribe"); ?>" type="submit">
		</div>
	</form>

<?php
}
?>
</div>