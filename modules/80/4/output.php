<?php

if (!function_exists('sendActivationMail')) {
    /**
     * Send activation mail.
     * @param \rex_yform_action_callback $yform YForm data
     */
    function sendActivationMail($yform): void
    {
        if (isset($yform->params['values'])) {
            $fields = [];
            foreach ($yform->params['values'] as $value) {
                if ('' !== $value->name) {
                    $fields[$value->name] = $value->value;
                }
            }

            $addon = rex_addon::get('multinewsletter');
            $user = FriendsOfRedaxo\MultiNewsletter\User::initByMail($fields['email']);
            if ($addon->hasConfig('sender') && $user instanceof FriendsOfRedaxo\MultiNewsletter\User) {
                $user->sendActivationMail(
                    (string) $addon->getConfig('sender'),
                    (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_sendername'),
                    (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_confirmsubject'),
                    (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_confirmcontent')
                );
                // Save to replace "," in group_ids list with pipes
                $user->save();
            }
        }
    }
}

$email = filter_var(rex_request('email', 'string'), FILTER_VALIDATE_EMAIL);
$activationkey = rex_request('activationkey', 'string');

// Deactivate emailobfuscator for POST od GET mail address
if (rex_addon::get('emailobfuscator')->isAvailable() && false !== $email) {
    emailobfuscator::whitelistEmail($email);
}

$cols_sm = 0 === (int) 'REX_VALUE[20]' ? 12 : (int) 'REX_VALUE[20]'; /** @phpstan-ignore-line */
$cols_md = 0 === (int) 'REX_VALUE[19]' ? 12 : (int) 'REX_VALUE[19]'; /** @phpstan-ignore-line */
$cols_lg = 0 === (int) 'REX_VALUE[18]' ? 12 : (int) 'REX_VALUE[18]'; /** @phpstan-ignore-line */
$offset_lg = (int) 'REX_VALUE[17]' > 0 ? ' mr-lg-auto ml-lg-auto ' : ''; /** @phpstan-ignore-line */

echo '<div class="col-12 col-sm-'. $cols_sm .' col-md-'. $cols_md .' col-lg-'. $cols_lg . $offset_lg .' yform">';

$addon = rex_addon::get('multinewsletter');

if (strlen($activationkey) > 5 && false !== $email) {
    // Handle activation key
    $user = FriendsOfRedaxo\MultiNewsletter\User::initByMail($email);
    if ($user instanceof FriendsOfRedaxo\MultiNewsletter\User && $user->activationkey === $activationkey) {
        echo '<p class="alert alert-success">'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_confirmation_successful', '') .'</p>';
        $user->activate();
    } elseif ($user instanceof FriendsOfRedaxo\MultiNewsletter\User && '0' === $user->activationkey) {
        echo '<p class="alert alert-danger">'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_already_confirmed', '') .'</p>';
    } else {
        echo '<p class="alert alert-danger">'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_key', '') .'</p>';
    }
} else {
    $ask_name = 'REX_VALUE[2]' === 'true' ? true : false; /** @phpstan-ignore-line */
    $ask_phone = 'REX_VALUE[3]' === 'true' ? true : false; /** @phpstan-ignore-line */

    // Show form
    $form_data = 'hidden|subscriptiontype|web
			hidden|status|0
			hidden|clang_id|'. rex_clang::getCurrentId() .'
			datestamp|createdate|createdate|mysql
			ip|createip
			action|copy_value|createdate|updatedate
			action|copy_value|createip|updateip
			generate_key|activationkey

			html||<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_action') .'<br><br></p>'. PHP_EOL;
    if ($ask_name) { /** @phpstan-ignore-line */
        $form_data .= 'choice|title|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_anrede', '') .'|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_title_-1', '').'=-1,'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_title_0', '').'=0,'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_title_1', '').'=1,'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_title_2', '').'=2|2|0|
			text|grad|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_grad', '') .'
			text|firstname|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_firstname', '') .' *|||{"required":"required"}
			text|lastname|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_lastname', '') .' *|||{"required":"required"}'. PHP_EOL;
    }
    $form_data .= 'text|email|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_email', '') .' *|||{"required":"required"}'. PHP_EOL;
    if ($ask_phone) { /** @phpstan-ignore-line */
        $form_data .= 'text|phone|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_phone', 'Telefon') . PHP_EOL;
    }
    $form_data .= 'html||<br><br>'. PHP_EOL;
    // Groups to be displayed
    $group_ids = (array) rex_var::toArray('REX_VALUE[1]');
    if (1 === count($group_ids)) {
        foreach ($group_ids as $group_id) {
            $form_data .= 'hidden|group_ids|'. $group_id . PHP_EOL;
        }
    } elseif (count($group_ids) > 1) {
        $group_options = [];
        foreach ($group_ids as $group_id) {
            $group = new FriendsOfRedaxo\MultiNewsletter\Group($group_id);
            $group_options[] = $group->name .'='. $group_id;
        }
        $form_data .= 'choice|group_ids|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_select_newsletter', '') .'|'. implode(',', $group_options) .'|1|1|
			html||<br><br>'. PHP_EOL;
    }

    $form_data .= 'checkbox|privacy_policy_accepted|'. preg_replace('#\\R+#', '<br>', (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_privacy_policy', '')) .' *<br><br>|0,1|0|{"required":"required"}
			html||<p>* '. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_compulsory', '') .'<br><br></p>
			html||<p> '. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_safety', '') .'<br><br></p>'. PHP_EOL;
    if ($ask_name) { /** @phpstan-ignore-line */
        $form_data .= 'validate|empty|firstname|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_firstname', '') .'
			validate|empty|lastname|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_name', '') . PHP_EOL;
    }
    if (rex_addon::get('yform_spam_protection')->isAvailable()) {
        // Spam protection message: editable per language in the addon settings
        // (lang_<clang>_spam_protection). {seconds} is replaced with the minimum
        // fill-out time configured in yform_spam_protection (higher of both
        // timers). A built-in multilingual fallback is used when unconfigured.
        $spam_config = rex_addon::get('yform_spam_protection');
        $spam_seconds = max(
            (int) $spam_config->getConfig('timer_session', 5),
            (int) $spam_config->getConfig('timer_form', 10)
        );
        $spam_fallbacks = [
            'de' => 'Ihre Anfrage wurde als möglicher Spam eingestuft. Bitte füllen Sie das versteckte Feld nicht aus und lassen Sie sich mindestens {seconds} Sekunden Zeit, bevor Sie das Formular absenden. Bitte warten Sie einen Moment und versuchen Sie es erneut.',
            'en' => 'Your request was classified as possible spam. Please do not fill in the hidden field and take at least {seconds} seconds before submitting the form. Please wait a moment and try again.',
            'nl' => 'Uw aanvraag is aangemerkt als mogelijke spam. Vul het verborgen veld niet in en neem minstens {seconds} seconden de tijd voordat u het formulier verzendt. Wacht een moment en probeer het opnieuw.',
        ];
        $spam_lang = substr((string) rex_clang::getCurrent()->getCode(), 0, 2);
        $spam_message = (string) $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_spam_protection', '');
        if ('' === trim($spam_message)) {
            $spam_message = $spam_fallbacks[$spam_lang] ?? $spam_fallbacks['en'];
        }
        // Remove pipes so the value cannot break the YForm pipe syntax.
        $spam_message = str_replace(['|', "\r", "\n"], [' ', ' ', ' '], $spam_message);
        $spam_message = str_replace('{seconds}', (string) $spam_seconds, $spam_message);
        $form_data .= '
            spam_protection|honeypot|Bitte nicht ausfüllen|'. $spam_message .'|0';
    }

    // Optional Altcha field (proof-of-work captcha, part of yform_spam_protection)
    if ('REX_VALUE[4]' === 'true' && rex_addon::get('yform_spam_protection')->isAvailable()) { /** @phpstan-ignore-line */
        $altcha_messages = [
            'de' => 'Die Verifizierung ist fehlgeschlagen. Bitte laden Sie die Seite neu und versuchen Sie es erneut.',
            'en' => 'Verification failed. Please reload the page and try again.',
            'nl' => 'Verificatie mislukt. Herlaad de pagina en probeer het opnieuw.',
        ];
        $altcha_lang = substr((string) rex_clang::getCurrent()->getCode(), 0, 2);
        $altcha_message = $altcha_messages[$altcha_lang] ?? $altcha_messages['en'];
        $form_data .= '
            altcha|altcha|'. $altcha_message .'
            html||<br>';
    }

    // Submit button is added last so it always sits below the Altcha widget.
    $form_data .= PHP_EOL .'submit|submit|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_subscribe', 'Send') .'|no_db'. PHP_EOL;

    $form_data .= 'validate|empty|email|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_email', '') .'
			validate|type|email|email|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_email', '') .'
			validate|unique|email|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_already_subscribed', '') .'|rex_375_user
			action|db|rex_375_user
			action|callback|sendActivationMail';

    $yform = new rex_yform();
    $yform->setFormData(trim($form_data));
    $yform->setObjectparams('Error-occured', $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_no_userdata', ''));
    $yform->setObjectparams('form_action', rex_getUrl(rex_article::getCurrentId(), rex_clang::getCurrentId()));
    $yform->setObjectparams('real_field_names', true);
    $yform->setObjectparams('form_name', 'multinewsletter_module_80_4_REX_SLICE_ID');

    // action - showtext
    $yform->setActionField('showtext', [
        $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_confirmation_sent', ''),
        '<a name="form-feedback"><div class="alert alert-success">',
        '</div>',
        '0'
    ]);

    echo $yform->getForm();
}

echo '</div>';
