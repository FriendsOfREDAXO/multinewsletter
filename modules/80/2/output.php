<?php
// Abmeldung aus Formular holen
$unsubscribe_mail = filter_input(INPUT_POST, 'unsubscribe_email', FILTER_VALIDATE_EMAIL);
if($unsubscribe_mail == "") {
	// Abmeldung aus URL holen
	$unsubscribe_mail = filter_input(INPUT_GET, 'unsubscribe', FILTER_VALIDATE_EMAIL);
}

// Deactivate emailobfuscator for POST od GET mail address
if (rex_addon::get('emailobfuscator')->isAvailable()) {
	emailobfuscator::whitelistEmail($unsubscribe_mail);
}

if(rex::isBackend()) {
	print '<p><b>Multinewsletter Abmeldung</b></p>';
	print '<p>Texte, Bezeichnungen bzw. Übersetzugen werden im <a href="index.php?page=multinewsletter&subpage=config">Multinewsletter Addon</a> verwaltet.</p>';

}
else {
	$addon = rex_addon::get('multinewsletter');
	
	print '<div class="col-12 col-lg-8 yform">';
	print '<h2>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_unsubscribe") .'</h2>';
	print '<br>';
	
	$showform = true;
	if($unsubscribe_mail != "") {
		$user = MultinewsletterUser::initByMail($unsubscribe_mail);
		#dump($user,$unsubscribe_mail);die;
		if($user !== false && $user->id > 0) {
			$user->unsubscribe();
			
			print "<p>". $addon->getConfig("lang_". rex_clang::getCurrentId() ."_status0") ."</p><br />";
			$showform = false;
		}
		else {
			print "<p>". $addon->getConfig("lang_". rex_clang::getCurrentId() ."_user_not_found") ."</p><br />";
		}
	}

	if($unsubscribe_mail == "" && (filter_input(INPUT_POST, 'unsubscribe_email') != "" || filter_input(INPUT_GET, 'unsubscribe'))) {
		print "<p>". $addon->getConfig("lang_". rex_clang::getCurrentId() ."_invalid_email") ."</p><br />";
	}
	
	if($showform) {
?>
		<form id="unsubscribe" action="<?php print rex_getURL(rex_article::getCurrentId(), rex_clang::getCurrentId()); ?>" method="post" name="unsubscribe" class="rex-yform">
			<div class="form-group yform-element">
				<label class="control-label" for="unsubscribe_email"><?php print $addon->getConfig("lang_". rex_clang::getCurrentId() ."_email"); ?></label>
				<input type="email" class="form-control" name="unsubscribe_email" value="" required>
			</div>
			<br />
			<div class="form-group yform-element">
				<input type="submit" class="btn btn-primary" name="unsubscribe_newsletter"
					value="<?php print $addon->getConfig("lang_". rex_clang::getCurrentId() ."_unsubscribe"); ?>" />
			</div>
		</form>
<?php
	}
	print '</div>';
}
?>