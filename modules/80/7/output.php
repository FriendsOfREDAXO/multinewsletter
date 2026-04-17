<?php
// Abmeldung holen
$unsubscribe_mail = filter_var(rex_request('unsubscribe', 'string'), FILTER_VALIDATE_EMAIL);

// Deactivate emailobfuscator for POST od GET mail address
if (rex_addon::get('emailobfuscator')->isAvailable() && false !== $unsubscribe_mail) {
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
    if (false !== $unsubscribe_mail) {
        $user = FriendsOfRedaxo\MultiNewsletter\User::initByMail($unsubscribe_mail);
        if ($user instanceof FriendsOfRedaxo\MultiNewsletter\User && $user->id > 0) {
            $user->unsubscribe();

            echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_status0') .'</p><br />';
            $showform = false;
        } else {
            echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_user_not_found') .'</p><br />';
        }
    }

    if (rex_request('unsubscribe', 'string', '') !== '' && false === $unsubscribe_mail) {
        echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_email') .'</p><br />';
    }

    if ($showform) {
?>
		<form id="unsubscribe" action="<?= rex_getUrl(rex_article::getCurrentId(), rex_clang::getCurrentId()) ?>" method="post" name="unsubscribe" class="rex-yform">
			<div class="form-group yform-element">
				<label class="control-label" for="unsubscribe"><?= (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_email') ?></label>
				<input type="email" class="form-control" name="unsubscribe" value="" required>
			</div>
			<br />
			<div class="form-group yform-element">
				<input type="submit" class="btn btn-primary" name="unsubscribe_newsletter"
					value="<?= (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_unsubscribe') ?>" />
			</div>
		</form>
<?php
    }
    echo '</div>';
}
?>