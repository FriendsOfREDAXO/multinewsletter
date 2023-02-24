<?php
// Abmeldung aus Formular holen
$unsubscribe_mail = filter_input(INPUT_POST, 'unsubscribe_email', FILTER_VALIDATE_EMAIL);
if ('' == $unsubscribe_mail) {
    // Abmeldung aus URL holen
    $unsubscribe_mail = filter_input(INPUT_GET, 'unsubscribe', FILTER_VALIDATE_EMAIL);
}

// Deactivate emailobfuscator for POST od GET mail address
if (rex_addon::get('emailobfuscator')->isAvailable()) {
    emailobfuscator::whitelistEmail($unsubscribe_mail);
}

if (rex::isBackend()) {
    echo '<p><b>Multinewsletter Abmeldung</b></p>';
    echo '<p>Texte, Bezeichnungen bzw. Übersetzugen werden im <a href="index.php?page=multinewsletter&subpage=config">Multinewsletter Addon</a> verwaltet.</p>';

} else {
    $addon = rex_addon::get('multinewsletter');

    echo '<div class="col-12 col-lg-8 yform">';
    echo '<h2>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_unsubscribe') .'</h2>';
    echo '<br>';

    $showform = true;
    if ('' != $unsubscribe_mail) {
        $user = MultinewsletterUser::initByMail($unsubscribe_mail);
        // dump($user,$unsubscribe_mail);die;
        if (false !== $user && $user->id > 0) {
            $user->unsubscribe();

            echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_status0') .'</p><br />';
            $showform = false;
        } else {
            echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_user_not_found') .'</p><br />';
        }
    }

    if ('' == $unsubscribe_mail && ('' != filter_input(INPUT_POST, 'unsubscribe_email') || filter_input(INPUT_GET, 'unsubscribe'))) {
        echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_email') .'</p><br />';
    }

    if ($showform) {
?>
		<form id="unsubscribe" action="<?= rex_getUrl(rex_article::getCurrentId(), rex_clang::getCurrentId()) ?>" method="post" name="unsubscribe" class="rex-yform">
			<div class="form-group yform-element">
				<label class="control-label" for="unsubscribe_email"><?= $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_email') ?></label>
				<input type="email" class="form-control" name="unsubscribe_email" value="" required>
			</div>
			<br />
			<div class="form-group yform-element">
				<input type="submit" class="btn btn-primary" name="unsubscribe_newsletter"
					value="<?= $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_unsubscribe') ?>" />
			</div>
		</form>
<?php
    }
    echo '</div>';
}
?>