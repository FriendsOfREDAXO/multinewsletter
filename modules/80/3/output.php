<div class="col-12 col-lg-8 yform">
<?php
// Anzuzeigende Gruppen IDs
$group_ids = (array) rex_var::toArray('REX_VALUE[1]');

$addon = rex_addon::get('multinewsletter');

$showform = true;

$email = filter_var(rex_request('email', 'string'), FILTER_VALIDATE_EMAIL);
$activationkey = rex_request('activationkey', 'string');

// Deactivate emailobfuscator for POST od GET mail address
if (rex_addon::get('emailobfuscator')->isAvailable() && false !== $email) {
    emailobfuscator::whitelistEmail($email);
}

if ('' === $activationkey && false !== $email) {
    $user = FriendsOfRedaxo\MultiNewsletter\User::initByMail($email);
    if ($user instanceof FriendsOfRedaxo\MultiNewsletter\User && $user->activationkey === $activationkey) {
        echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_confirmation_successful') .'</p>';
        $user->activate();
    } elseif ($user instanceof FriendsOfRedaxo\MultiNewsletter\User && '0' === $user->activationkey) {
        echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_already_confirmed') .'</p>';
    } else {
        echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_key') .'</p>';
    }
    $showform = false;
}

$form_groups = filter_input_array(INPUT_POST, ['groups' => ['filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY]]);
$group_ids = is_array($form_groups['groups']) ? $form_groups['groups'] : [];

$messages = [];

if ('' !== filter_input(INPUT_POST, 'submit')) {
    $save = true;
    // Fehlermeldungen finden
    if (false === $email) {
        $messages[] = $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_email');
    }

    // Userobjekt deklarieren
    $user = false;
    if (count($messages) > 0) {
        echo '<p><b>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_no_userdata') .'</b></p>';
        echo '<ul>';
        foreach ($messages as $message) {
            echo '<li><b>'. $message .'</b></li>';
        }
        echo '</ul>';
        echo '<br>';

        $save = false;
    } elseif (false !== $email) {
        // Ist Benutzer schon in der Newslettergruppe?
        $user = FriendsOfRedaxo\MultiNewsletter\User::initByMail($email);
        if ($user instanceof FriendsOfRedaxo\MultiNewsletter\User && $user->id > 0 && 1 === $user->status) {
            $not_already_subscribed = [];
            if (count($user->group_ids) > 0 && count($group_ids) > 0) {
                foreach ($group_ids as $group_id) {
                    if (!in_array($group_id, $user->group_ids, true)) {
                        $not_already_subscribed[] = $group_id;
                    }
                }
            }
            if (count($group_ids) > 0 && 0 === count($not_already_subscribed) && 1 === $user->privacy_policy_accepted) {
                echo '<p><b>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_already_subscribed') .'</b></p>';
                $save = false;
            }

            $showform = false;
        }
    }

    if ($save) {
        // Benutzer speichern
        if ($user instanceof FriendsOfRedaxo\MultiNewsletter\User) {
            $user->clang_id = rex_clang::getCurrentId();
        } else {
            $user = FriendsOfRedaxo\MultiNewsletter\User::factory(
                (string) filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
                0,
                '',
                '',
                '',
                rex_clang::getCurrentId(),
            );
        }
        $user->createdate = date('Y-m-d H:i:s');
        $user->createip = rex_request::server('REMOTE_ADDR', 'string');
        $user->group_ids = $group_ids;
        $user->status = 0;
        $user->subscriptiontype = 'web';
        $user->activationkey = (string) random_int(100000, 999999);
        $user->privacy_policy_accepted = 1 === rex_request('privacy_policy', 'int') ? 1 : 0;
        $user->save();

        // Aktivierungsmail senden und Hinweis ausgeben
        $user->sendActivationMail(
            (string) $addon->getConfig('sender'),
            (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_sendername'),
            (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_confirmsubject'),
            (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_confirmcontent')
        );
        echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_confirmation_sent') .'</p>';

        $showform = false;
    }
}

if ($showform) {
    if (0 === count($messages)) {
        echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_action') .'</p>';
    }
?>
	<form action="<?= rex_getUrl(rex_article::getCurrentId(), rex_clang::getCurrentId()) ?>" method="post" name="subscribe" class="rex-yform">
		<div class="form-group yform-element" id="yform-formular-email">
			<label class="control-label" for="email"><?= (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_email') ?> *</label>
			<input class="form-control" name="email" id="email" value="<?= false !== $email ? $email : '' ?>" type="email" maxlength="100" required>
		</div>
		<?php
            if (1 === count($group_ids)) {
                foreach ($group_ids as $group_id) {
                    echo '<input type="hidden" name="groups['. $group_id.']" value="'. $group_id .'" />';
                }
            } elseif (count($group_ids) > 1) {
                echo '<br clear="all"><p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_select_newsletter') .'</p>';

                foreach ($group_ids as $group_id) {
                    $group = new FriendsOfRedaxo\MultiNewsletter\Group($group_id);
                    if ('' !== $group->name) {
                        echo '<div class="form-group yform-element" id="yform-formular">';
                        $checked = '';
                        if (in_array($group_id, $group_ids, true)) {
                            $checked = ' checked="checked"';
                        }
                        echo '<input class="checkbox" name="groups['. $group_id .']" id="yform-formular-'. $group_id .'" value="'. $group_id .'" type="checkbox"'. $checked .'>';
                        echo '<label class="control-label" for="groups['. $group_id .']">'. $group->name .'</label>';
                        echo '</div>';
                    }
                }
            }

            // Privacy policy
            echo '<div class="form-group yform-element" id="yform-formular">';
            echo '<label class="control-label" for="privacy_policy">';
            echo '<input class="checkbox" name="privacy_policy" id="yform-formular-privacy-policy" value="1" type="checkbox"'. (1 === rex_request('privacy_policy', 'int') ? ' checked="checked"' : '') .' required>';
            echo $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_privacy_policy') .' *</label>';
            echo '</div>';
        ?>
		<p><?= (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_compulsory') ?></p>
		<p><?= (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_safety') ?></p>
		<div class="form-group yform-element">
			<input class="btn btn-primary" name="submit" id="submit" value="<?= (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_subscribe') ?>" type="submit">
		</div>
	</form>

<?php
}
?>
</div>