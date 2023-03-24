<?php

if (!function_exists('unsubscribeYForm')) {
    /**
     * Unsubscribe user.
     * @param string[] $yform YForm data
     */
    function unsubscribeYForm($yform)
    {
        if (isset($yform->params['values'])) {
            $fields = [];
            foreach ($yform->params['values'] as $value) {
                if ('' != $value->name) {
                    $fields[$value->name] = $value->value;
                }
            }

            unsubscribe($fields['email']);
        }
    }
}

if (!function_exists('unsubscribe')) {
    /**
     * Unsubscribe user.
     * @param string $email Email Address
     */
    function unsubscribe($email)
    {
        $addon = rex_addon::get('multinewsletter');
        if ('' != filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $user = MultinewsletterUser::initByMail($email);
            if (false !== $user) {
                $user->unsubscribe();
                echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_status0', '') .'</p><br />';
            } else {
                echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_user_not_found', '') .'</p><br />';
            }
        } else {
            echo '<p>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_email', '') .'</p><br />';
        }
    }
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

    $cols_sm = 0 === (int) 'REX_VALUE[20]' ? 12 : (int) 'REX_VALUE[20]'; /** @phpstan-ignore-line */
    $cols_md = 0 === (int) 'REX_VALUE[19]' ? 12 : (int) 'REX_VALUE[19]'; /** @phpstan-ignore-line */
    $cols_lg = 0 === (int) 'REX_VALUE[18]' ? 12 : (int) 'REX_VALUE[18]'; /** @phpstan-ignore-line */
    $offset_lg = (int) 'REX_VALUE[17]' > 0 ? ' mr-lg-auto ml-lg-auto ' : ''; /** @phpstan-ignore-line */

    echo '<div class="col-12 col-sm-'. $cols_sm .' col-md-'. $cols_md .' col-lg-'. $cols_lg . $offset_lg .' yform">';
    echo '<h2>'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_unsubscribe', '') .'</h2>';
    echo '<br>';

    $unsubscribe_mail = filter_input(INPUT_GET, 'unsubscribe', FILTER_VALIDATE_EMAIL);
    if ('' != $unsubscribe_mail) {
        unsubscribe($unsubscribe_mail);
    } else {
        // Show form
        $form_data .= 'text|email|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_email', '') .' *|||{"required":"required"}
				html||<br><br>
				html||<p>* '. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_compulsory', '') .'<br><br></p>

				submit|submit|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_unsubscribe', '') .'|no_db
				validate|empty|email|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_email', '') .'
				validate|type|email|email|'. $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_invalid_email', '') .'

				action|callback|unsubscribeYForm';

        $yform = new rex_yform();
        $yform->setFormData(trim($form_data));
        $yform->setObjectparams('csrf_protection', false);
        $yform->setObjectparams('Error-occured', $addon->getConfig('lang_'. rex_clang::getCurrentId() .'_no_userdata', ''));
        $yform->setObjectparams('form_action', rex_getUrl(rex_article::getCurrentId(), rex_clang::getCurrentId()));
        $yform->setObjectparams('form_name', 'multinewsletter_module_80_5_'. random_int(1, 100));
        $yform->setObjectparams('real_field_names', true);

        echo $yform->getForm();
    }
    echo '</div>';
}
