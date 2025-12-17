<div class="col-12 col-lg-8 yform">
<?php
// Anzuzeigende Gruppen IDs
$group_ids = rex_var::toArray('REX_VALUE[1]');

$addon = rex_addon::get('multinewsletter');

$showform = true;

// Deactivate emailobfuscator for POST od GET mail address
if (rex_addon::get('emailobfuscator')->isAvailable()) {
    if (false !== filter_var(rex_request('email'), FILTER_VALIDATE_EMAIL)) {
        emailobfuscator::whitelistEmail((string) filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    }
}

if (filter_input(INPUT_GET, 'activationkey', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]) > 0 && false !== filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL)) {
    $user = FriendsOfRedaxo\MultiNewsletter\User::initByMail((string) filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL));
    if ($user instanceof FriendsOfRedaxo\MultiNewsletter\User && $user->activationkey === filter_input(INPUT_GET, 'activationkey')) {
        echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_confirmation_successful') .'</p>';
        $user->activate();
    } elseif ($user instanceof FriendsOfRedaxo\MultiNewsletter\User && '' === $user->activationkey) {
        echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_already_confirmed') .'</p>';
    } else {
        echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_key') .'</p>';
    }
    $showform = false;
}

$form_groups = filter_input_array(INPUT_POST, ['groups' => ['filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY]]);
$group_ids = is_array($form_groups) && is_array($form_groups['groups']) ? $form_groups['groups'] : $group_ids;

$messages = [];
if (null !== filter_input(INPUT_POST, 'submit')) {
    $save = true;
    // Fehlermeldungen finden
    if (false === filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
        $messages[] = $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_email');
    }
    if (null === filter_input(INPUT_POST, 'firstname') || strlen((string) filter_input(INPUT_POST, 'firstname')) > 30) {
        $messages[] = $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_firstname');
    }
    if (null === filter_input(INPUT_POST, 'lastname') || strlen((string) filter_input(INPUT_POST, 'lastname')) > 30) {
        $messages[] = $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_lastname');
    }
    if (0 === count($group_ids)) {
        $messages[] = $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_nogroup_selected');
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
    } elseif (false !== filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) && null !== filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
        // Ist Benutzer schon in der Newslettergruppe?
        $user = FriendsOfRedaxo\MultiNewsletter\User::initByMail(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
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

    if (true === $save) {
        // nur, wenn noch nicht gesendet
        if (1 !== rex_request::session('newsletteranmeldung_gesendet', 'int')) {
            // Benutzer speichern
            if ($user instanceof FriendsOfRedaxo\MultiNewsletter\User) {
                $user->title = rex_request::post('anrede', 'int');
                $user->firstname = rex_request::post('firstname', 'string');
                $user->lastname = rex_request::post('lastname', 'string');
                $user->clang_id = rex_clang::getCurrentId();
            } else {
                $user = FriendsOfRedaxo\MultiNewsletter\User::factory(
                    (string) filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
                    rex_request::post('anrede', 'int'),
                    rex_request::post('grad', 'string'),
                    rex_request::post('firstname', 'string'),
                    rex_request::post('lastname', 'string'),
                    rex_clang::getCurrentId(),
                );
            }
            $user->createdate = date('Y-m-d H:i:s');
            $user->createip = rex_request::server('REMOTE_ADDR', 'string');
            $user->group_ids = $group_ids;
            $user->status = 0;
            $user->subscriptiontype = 'web';
            $user->activationkey = (string) random_int(100000, 999999);
            $user->privacy_policy_accepted = 1 === filter_input(INPUT_POST, 'privacy_policy', FILTER_VALIDATE_INT) ? 1 : 0;
            $user->save();

            // Aktivierungsmail senden und Hinweis ausgeben
            $user->sendActivationMail(
                (string) $addon->getConfig('sender'),
                (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_sendername'),
                (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_confirmsubject'),
                (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_confirmcontent'),
            );
            echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_confirmation_sent') .'</p>';
            // reload verhindern
            rex_request::setSession('newsletteranmeldung_gesendet', 1);
        } else {
            // Aktivierungsmail wurde schon gesendet
            // ToDo: Meldung in config aufnehmen
            echo '<p>Die Bestätigungsmail wurde bereits gesendet.</p>';
        }
        $showform = false;
    }
}

if ($showform) {
    // Session zum Senden freigeben
    rex_request::unsetSession('newsletteranmeldung_gesendet');
    if (0 === count($messages)) {
        echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_action') .'</p>';
    }
?>
	<form action="<?= rex_getUrl(rex_article::getCurrentId(), rex_clang::getCurrentId()) ?>" method="post" name="subscribe" class="rex-yform">
		<div class="form-group yform-element" id="yform-formular-anrede">
			<label class="select" for="anrede"><?= (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_anrede') ?></label>
			<select class="select" name="anrede" size="1">
                <?php
                    $title_ids = [-1, 0, 1, 2];
                    foreach ($title_ids as $title_id) {
                        echo '<option value="'. $title_id .'" '. ($title_id === filter_input(INPUT_POST, 'anrede', FILTER_VALIDATE_INT) ? ' selected' : '') .'>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_title_'. $title_id) .'</option>';
                    }
                ?>
			</select>
		</div>
		<div class="form-group yform-element" id="yform-formular-grad">
			<label class="control-label" for="grad"><?= (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_grad') ?></label>
			<input class="form-control" name="grad" value="<?= filter_input(INPUT_POST, 'grad') ?>" type="text" maxlength="15">
		</div>
		<div class="form-group yform-element" id="yform-formular-firstname">
			<label class="control-label" for="firstname"><?= (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_firstname') ?> *</label>
			<input class="form-control" name="firstname" value="<?= filter_input(INPUT_POST, 'firstname') ?>" type="text" maxlength="30" required>
		</div>
		<div class="form-group yform-element" id="yform-formular-lastname">
			<label class="control-label" for="lastname"><?= (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_lastname') ?> *</label>
			<input class="form-control" name="lastname" value="<?= filter_input(INPUT_POST, 'lastname') ?>" type="text" maxlength="30" required>
		</div>
		<div class="form-group yform-element" id="yform-formular-email">
			<label class="control-label" for="email"><?= (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_email') ?> *</label>
			<input class="form-control" name="email" value="<?= filter_input(INPUT_POST, 'email') ?>" type="email" maxlength="100" required>
		</div>
		<?php if ('REX_VALUE[2]' === 'true') { /** @phpstan-ignore-line */ ?>
		<div class="form-group yform-element" id="yform-formular-phone">
			<label class="control-label" for="phone"><?= (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_phone', 'Telefon') ?></label>
			<input class="form-control" name="phone" value="<?= filter_input(INPUT_POST, 'phone') ?>" type="tel" maxlength="30">
		</div>
		<?php } ?>
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
                        echo '<p class="formcheckbox formlabel-group" id="yform-formular">';
                        $checked = '';
                        if (in_array($group_id, $group_ids, true)) {
                            $checked = ' checked="checked"';
                        }
                        echo '<input class="checkbox" name="groups['. $group_id .']" id="yform-formular-'. $group_id .'" value="'. $group_id .'" type="checkbox"'. $checked .'>';
                        echo '<label class="checkbox" for="groups['. $group_id .']">'. $group->name .'</label>';
                        echo '</p>';
                    }
                }
            }
            // Privacy policy
            echo '<div class="form-group yform-element" id="yform-formular">';
            echo '<label class="control-label" for="privacy_policy">';
            echo '<input class="checkbox" name="privacy_policy" value="1" type="checkbox"'. (1 === filter_input(INPUT_POST, 'privacy_policy', FILTER_VALIDATE_INT) ? ' checked="checked"' : '') .' required>';
            echo (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_privacy_policy') .' *</label>';
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