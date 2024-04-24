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
        echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_confirmation_successful', '') .'</p>';
        $user->activate();
    } elseif ($user instanceof FriendsOfRedaxo\MultiNewsletter\User && '0' === $user->activationkey) {
        echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_already_confirmed', '') .'</p>';
    } else {
        echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_key', '') .'</p>';
    }
} else {
    $ask_name = 'REX_VALUE[2]' === 'true' ? true : false; /** @phpstan-ignore-line */

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
    $form_data .= 'text|email|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_email', '') .' *|||{"required":"required"}
			html||<br><br>'. PHP_EOL;
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
			html||<p> '. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_safety', '') .'<br><br></p>

			submit|submit|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_subscribe', 'Send') .'|no_db'. PHP_EOL;
    if ($ask_name) { /** @phpstan-ignore-line */
        $form_data .= 'validate|empty|firstname|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_firstname', '') .'
			validate|empty|lastname|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_name', '') . PHP_EOL;
    }
    if (rex_addon::get('yform_spam_protection')->isAvailable()) {
        $form_data .= '
            spam_protection|honeypot|Bitte nicht ausfüllen|'. \Sprog\Wildcard::get('d2u_helper_module_form_validate_spam_detected') .'|0';
    }

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
    $yform->setObjectparams('form_name', 'multinewsletter_module_80_4_'. $this->getCurrentSlice()->getId()); /** @phpstan-ignore-line */

    // action - showtext
    $yform->setActionField('showtext', [$addon->getConfig('lang_'. rex_clang::getCurrentId() .'_confirmation_sent', '')]);

    echo $yform->getForm();
}

echo '</div>';
