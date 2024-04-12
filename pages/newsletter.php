<?php
$newsletterManager = new MultinewsletterNewsletterManager((int) rex_config::get('multinewsletter', 'max_mails'));
// First do reset action
if('' !== rex_request::get('reset', 'string')) {
    // 0 = reset complete sendlist
    $newsletterManager->reset(rex_request::get('reset', 'int', 0));
}

// Autosend stuff
if(!rex_addon::get('cronjob')->isAvailable() || 'active' !== (string) rex_config::get('multinewsletter', 'autosend', 'inactive') || '' === (string) rex_config::get('multinewsletter', 'admin_email', '')) {
    // If autosend is not correctly configured
    echo rex_view::warning(rex_i18n::msg('multinewsletter_newsletter_send_cron_not_available'));
} else {
    $autosend_message = '';
    // if automatic send in background is requested
    if('' !== filter_input(INPUT_POST, 'send_cron')) {
        // Send in background via CronJob
        foreach($newsletterManager->archives as $archive) {
            $archive->setAutosend();
        }
        $autosend_message = '<p>'. rex_i18n::msg('multinewsletter_newsletter_send_cron_active') .'</p><br>';
        // Reset send settings
        rex_request::unsetSession('multinewsletter');
    } else {
        // Autosend status message if autosend is active
        $newsletterManager_autosend = new MultinewsletterNewsletterManager((int) rex_config::get('multinewsletter', 'max_mails'), true);
        if($newsletterManager_autosend->countRemainingUsers() > 0) {
            $autosend_message = '<p>'. rex_i18n::msg('multinewsletter_newsletter_send_cron_warning') .'</p><br>';
        }
    }
    if('' !== $autosend_message) {
        // Detailed newsletter information
        $newsletterManager_autosend = new MultinewsletterNewsletterManager((int) rex_config::get('multinewsletter', 'max_mails'), true);
        $autosend_message .= '<form action="'. rex_url::currentBackendPage() .'" method="post" name="multinewsletter-cron-abort"><ul>';
        foreach($newsletterManager_autosend->archives as $autosend_archive) {
            $autosend_message .= '<li>'. $autosend_archive->countRemainingUsers() .' '. rex_i18n::msg('multinewsletter_archive_recipients') .': '. $autosend_archive->subject .' ('. $autosend_archive->sender_name.') <button class="btn btn-delete" style="margin: 5px 15px;" type="submit" name="reset" value="'. $autosend_archive->id .'">'. rex_i18n::msg('multinewsletter_newsletter_send_cron_abort') .'</button></li>';
        }
        $autosend_message .= '</ul></form>';
        echo rex_view::warning($autosend_message);
    }
}

$messages = [];

// Suchkriterien in Session schreiben
$session_multinewsletter = rex_request::session('multinewsletter', 'array');
if(!array_key_exists('newsletter', $session_multinewsletter)) {
    $session_multinewsletter['newsletter'] = [];
}
if(!array_key_exists('sender_name', $session_multinewsletter['newsletter'])) {
    $session_multinewsletter['newsletter']['sender_name'] = [];
}


// Vorauswahl der Gruppe
if(rex_request::get('preselect_group', 'int', 0) > 0) {
    $session_multinewsletter['newsletter']['preselect_group'] = rex_request::get('preselect_group', 'int');
} elseif(!array_key_exists('preselect_group', $session_multinewsletter['newsletter'])
    || $session_multinewsletter['newsletter']['preselect_group'] < 0
    || '' === $session_multinewsletter['newsletter']['preselect_group']) {
    $session_multinewsletter['newsletter']['preselect_group'] = 0;
}

// Status des Sendefortschritts. Bedeutungen
if((!array_key_exists('status', $session_multinewsletter['newsletter']) && 0 === $newsletterManager->countRemainingUsers())
        || null !== filter_input(INPUT_POST, 'reset')) {
    // 0 = Aufruf des neuen Formulars
    $session_multinewsletter['newsletter']['status'] = 0;
}
elseif(null !== filter_input(INPUT_POST, 'sendtestmail')) {
    // 1 = Testmail wurde verschickt
    // Status wird säter nur gesetzt, wenn kein Fehler beim Versand auftrat
} elseif(null !== filter_input(INPUT_POST, 'prepare')) {
    // 2 = Benutzer wurden vorbereitet
    // Status wird säter nur gesetzt, wenn kein Fehler beim Vorbereiten auftrat
} elseif(null !== filter_input(INPUT_POST, 'send') || $newsletterManager->countRemainingUsers() > 0) {
    // 3 = Versand gestartet
    $session_multinewsletter['newsletter']['status'] = 3;
}

// Ausgewählter Artikel
$form_link = filter_input_array(INPUT_POST, ['REX_INPUT_LINK' => ['filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY]]);
if(null !== $form_link && is_array($form_link['REX_INPUT_LINK']) && array_key_exists(1, $form_link['REX_INPUT_LINK'])) {
    $session_multinewsletter['newsletter']['article_id'] = $form_link['REX_INPUT_LINK'][1];
    $default_test_article = rex_article::get($form_link['REX_INPUT_LINK'][1]);
    if ($default_test_article instanceof rex_article) {
        $session_multinewsletter['newsletter']['article_name'] = $default_test_article->getName();
    }
} elseif(!array_key_exists('article_id', $session_multinewsletter['newsletter'])) {
    if($newsletterManager->countRemainingUsers() > 0) {
        // If there is a non-autosend archive, take article name from archive, article ID is not relevant
        $archives = MultinewsletterNewsletterManager::getArchivesToSend(true);
        $session_multinewsletter['newsletter']['article_id'] = 0;
        $session_multinewsletter['newsletter']['article_name'] = $archives[0]->subject;
    } else {
        // Otherwise take article and and article ID from settings
        $session_multinewsletter['newsletter']['article_id'] = (int) rex_config::get('multinewsletter', 'default_test_article');
        $default_test_article = rex_article::get($session_multinewsletter['newsletter']['article_id']);
        if ($default_test_article instanceof rex_article) {
            $session_multinewsletter['newsletter']['article_name'] = $default_test_article->getName();
        }
    }
}

// Ausgewählter Sender E-Mail
if('' !== rex_request::get('sender_email', 'string') && false !== filter_var(rex_request::get('sender_email', 'string'), FILTER_VALIDATE_EMAIL)) {
    $session_multinewsletter['newsletter']['sender_email'] = rex_request::get('sender_email', 'string');
} elseif(!array_key_exists('sender_email', $session_multinewsletter['newsletter'])) {
    $session_multinewsletter['newsletter']['sender_email'] = rex_config::get('multinewsletter', 'sender');
}

// Ausgewählter Sender Name
$form_sendernamen = filter_input_array(INPUT_POST, ['sender_name' => ['flags' => FILTER_REQUIRE_ARRAY]]);
foreach(rex_clang::getAll() as $rex_clang) {
    if(isset($form_sendernamen['sender_name'][$rex_clang->getId()])) {
        $session_multinewsletter['newsletter']['sender_name'][$rex_clang->getId()] = $form_sendernamen['sender_name'][$rex_clang->getId()];
    } elseif(!isset($session_multinewsletter['newsletter']['sender_name'][$rex_clang->getId()])) {
        if(rex_config::has('multinewsletter', 'lang_'. $rex_clang->getId() .'_sendername')) {
            $session_multinewsletter['newsletter']['sender_name'][$rex_clang->getId()] = rex_config::get('multinewsletter', 'lang_'. $rex_clang->getId() .'_sendername');
        } else {
            $session_multinewsletter['newsletter']['sender_name'][$rex_clang->getId()] = '';
        }
    }
}

// Reply-to E-Mail
if('' !== rex_request::get('reply_to_email', 'string')) {
    $reply_to_email = filter_var(rex_request::get('reply_to_email', 'string'), FILTER_VALIDATE_EMAIL);
    $session_multinewsletter['newsletter']['reply_to_email'] = false !== $reply_to_email ? $reply_to_email : '';
} elseif(!array_key_exists('reply_to_email', $session_multinewsletter['newsletter'])) {
    $session_multinewsletter['newsletter']['reply_to_email'] = rex_config::get('multinewsletter', 'reply_to');
}

// Testmail Empfäger E-Mail
if('' !== rex_request::get('testemail', 'string')) {
    $testemail = filter_var(rex_request::get('testemail', 'string'), FILTER_VALIDATE_EMAIL);
    $session_multinewsletter['newsletter']['testemail'] = false !== $testemail ? $testemail : '';
} elseif(!array_key_exists('testemail', $session_multinewsletter['newsletter'])) {
    $session_multinewsletter['newsletter']['testemail'] = rex_config::get('multinewsletter', 'default_test_email');
}

// Testmail Empfäger Titel
if(rex_request::get('testtitle', 'int') > 0) {
    $session_multinewsletter['newsletter']['testtitle'] = rex_request::get('testtitle', 'int');
} elseif(!array_key_exists('testtitle', $session_multinewsletter['newsletter'])) {
    $session_multinewsletter['newsletter']['testtitle'] = rex_config::get('multinewsletter', 'default_test_anrede');
}

// Testmail Empfäger Akademischer Grad
if('' !== rex_request::get('testgrad', 'string')) {
    $session_multinewsletter['newsletter']['testgrad'] = rex_request::get('testgrad', 'string');
} elseif(!array_key_exists('testgrad', $session_multinewsletter['newsletter'])) {
    $session_multinewsletter['newsletter']['testgrad'] = '';
}

// Testmail Empfäger Vorname
if('' !== rex_request::get('testfirstname', 'string')) {
    $session_multinewsletter['newsletter']['testfirstname'] = rex_request::get('testfirstname', 'string');
} elseif(!array_key_exists('testfirstname', $session_multinewsletter['newsletter'])) {
    $session_multinewsletter['newsletter']['testfirstname'] = rex_config::get('multinewsletter', 'default_test_vorname');
}

// Testmail Empfäger Nachname
if('' !== rex_request::get('testlastname', 'string')) {
    $session_multinewsletter['newsletter']['testlastname'] = rex_request::get('testlastname', 'string');
} elseif(!array_key_exists('testlastname', $session_multinewsletter['newsletter'])) {
    $session_multinewsletter['newsletter']['testlastname'] = rex_config::get('multinewsletter', 'default_test_nachname');
}

// Testmail Empfäger Sprache
if(rex_request::get('testlanguage', 'int') > 0) {
    $session_multinewsletter['newsletter']['testlanguage'] = rex_request::get('testlanguage', 'int');
} elseif(!array_key_exists('testlanguage', $session_multinewsletter['newsletter'])) {
    $session_multinewsletter['newsletter']['testlanguage'] = (int) rex_config::get('multinewsletter', 'default_test_sprache');
}

// Für den Versand ausgewählte Gruppen
$form_groups = filter_input_array(INPUT_POST, ['group' => ['filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY]]);
if(null !== $form_groups && is_array($form_groups['group']) && count($form_groups['group']) > 0) {
    $session_multinewsletter['newsletter']['groups'] = $form_groups['group'];
} elseif(!array_key_exists('groups', $session_multinewsletter['newsletter']) || !is_array($session_multinewsletter['newsletter']['groups'])) {
    $session_multinewsletter['newsletter']['groups'] = [$session_multinewsletter['newsletter']['preselect_group']];
}

// Attachments
$attachments = trim(rex_post('attachments', 'string'));
if('' !== $attachments) {
    $session_multinewsletter['newsletter']['attachments'] = $attachments;
} elseif((!array_key_exists('attachments', $session_multinewsletter['newsletter']) || '' === $session_multinewsletter['newsletter']['attachments']) && $session_multinewsletter['newsletter']['article_id'] > 0 && rex_article::get((int) $session_multinewsletter['newsletter']['article_id']) instanceof rex_article) {
    $session_multinewsletter['newsletter']['attachments'] = rex_article::get((int) $session_multinewsletter['newsletter']['article_id'])->getValue('art_newsletter_attachments');
}

// Für den Versand ausgewählte Empfänger
$recipients = array_filter(rex_post('recipients', 'array', []));
if(count($recipients) > 0) {
    $session_multinewsletter['newsletter']['man_recipients'] = $recipients;
}

rex_request::setSession('multinewsletter', $session_multinewsletter);

// Die Gruppen laden
$newsletter_groups = MultinewsletterGroup::getAll();

$time_started = time();
$maxtimeout = (int) ini_get('max_execution_time');
if(0 === $maxtimeout) {
    $maxtimeout = 20;
}

// Send test mail
if('' === rex_request('sendtestmail', 'string')) {
    // Exists article and is it online
    if((int) $session_multinewsletter['newsletter']['article_id'] <= 0) {
        $messages[] = rex_i18n::msg('multinewsletter_error_noarticle');
    } else {
        $temp = rex_article::get(
            $session_multinewsletter['newsletter']['article_id'],
            $session_multinewsletter['newsletter']['testlanguage'],
        );
        if(!$temp instanceof rex_article || !$temp->isOnline()) {
            $messages[] = rex_i18n::msg('multinewsletter_error_articlenotfound',
                $session_multinewsletter['newsletter']['article_id'],
                rex_clang::get($session_multinewsletter['newsletter']['testlanguage']) instanceof rex_clang ? rex_clang::get($session_multinewsletter['newsletter']['testlanguage'])->getName() : ''
            );
        }
    }

    // Send
    if(false === filter_var($session_multinewsletter['newsletter']['testemail'], FILTER_SANITIZE_EMAIL)) {
        $messages[] = rex_i18n::msg('multinewsletter_error_invalidemail',
            $session_multinewsletter['newsletter']['testemail']);
    }

    if(0 === count($messages)) {
        $testnewsletter = MultinewsletterNewsletter::factory($session_multinewsletter['newsletter']['article_id'],
            $session_multinewsletter['newsletter']['testlanguage']);

        $testuser = MultinewsletterUser::factory($session_multinewsletter['newsletter']['testemail'],
            $session_multinewsletter['newsletter']['testtitle'],
            $session_multinewsletter['newsletter']['testgrad'],
            $session_multinewsletter['newsletter']['testfirstname'],
            $session_multinewsletter['newsletter']['testlastname'],
            $session_multinewsletter['newsletter']['testlanguage']);

        $testnewsletter->sender_email = $session_multinewsletter['newsletter']['sender_email'];
        $testnewsletter->sender_name = $session_multinewsletter['newsletter']['sender_name'][$session_multinewsletter['newsletter']['testlanguage']];
        $testnewsletter->reply_to_email = false !== filter_var($session_multinewsletter['newsletter']['reply_to_email'], FILTER_SANITIZE_EMAIL) ? filter_var($session_multinewsletter['newsletter']['reply_to_email'], FILTER_SANITIZE_EMAIL) : $session_multinewsletter['newsletter']['sender_email'];
        $testnewsletter->attachments = explode(',', $attachments);

        $sendresult = $testnewsletter->sendTestmail($testuser, $session_multinewsletter['newsletter']['article_id']);

        if(!$sendresult) {
            $messages[] = rex_i18n::msg('multinewsletter_error_senderror');
        } else {
            $session_multinewsletter['newsletter']['status'] = 1;
        }
    }
}
// Adressen vorbereiten
elseif('' !== rex_request::get('prepare', 'string')) {
    if(0 === count($session_multinewsletter['newsletter']['groups']) && 0 === count($session_multinewsletter['newsletter']['man_recipients'])) {
        $messages[] = rex_i18n::msg('multinewsletter_error_nogroupselected');
    }

    if(0 === count($messages)) {
        $offline_lang_ids = $newsletterManager->prepare($session_multinewsletter['newsletter']['groups'],
            $session_multinewsletter['newsletter']['article_id'],
            null !== MultinewsletterNewsletter::getFallbackLang() ? MultinewsletterNewsletter::getFallbackLang() : 0,
            $session_multinewsletter['newsletter']['man_recipients'],
            $session_multinewsletter['newsletter']['attachments']);

        if(count($offline_lang_ids) > 0) {
            $offline_langs = [];
            foreach($offline_lang_ids as $clang_id) {
                if(rex_clang::get($clang_id) instanceof rex_clang) {
                    $offline_langs[] = rex_clang::get($clang_id)->getName();
                }
            }
            if(null === MultinewsletterNewsletter::getFallbackLang() || in_array(MultinewsletterNewsletter::getFallbackLang(), $offline_lang_ids, true)) {
                $messages[] = rex_i18n::msg('multinewsletter_error_someclangsoffline', implode(', ', $offline_langs));
            } else {
                $messages[] = rex_i18n::msg('multinewsletter_error_someclangsdefault', implode(', ', $offline_langs));
            }
        }
        $session_multinewsletter['newsletter']['status'] = 2;
    }
}
// Versand des Newsletters
elseif('' !== rex_request::get('send', 'string')) {
    $number_mails_send = $newsletterManager->countRemainingUsers() % (int) rex_config::get('multinewsletter', 'max_mails');
    if(0 === $number_mails_send) {
        $number_mails_send = (int) rex_config::get('multinewsletter', 'max_mails');
    }
    $sendresult = $newsletterManager->send($number_mails_send);
    if(is_array($sendresult)) {
        $messages[] = rex_i18n::msg('multinewsletter_error_send_incorrect_user') .' '. implode(', ', $sendresult);
    }
    if(count($newsletterManager->last_send_users) > 0) {
        $message = rex_i18n::msg('multinewsletter_expl_send_success').'<br /><ul>';
        foreach($newsletterManager->last_send_users as $user) {
            $message .= '<li>';
            if('' !== $user->firstname || '' !== $user->lastname) {
                $message .= $user->firstname .' '. $user->lastname .': ';
            }
            $message .= $user->email .'</li>';
        }
        $message .= '</ul>';
        echo rex_view::success($message);
    }
    $session_multinewsletter['newsletter']['status'] = 3;
}

// Fehler ausgeben
foreach($messages as $msg) {
    echo rex_view::error($msg);
}

if(class_exists(rex_mailer::class)) {
?>
	<form action="<?= rex_url::currentBackendPage() ?>" method="post" name="MULTINEWSLETTER">
		<div class="panel panel-edit">
			<header class="panel-heading"><div class="panel-title"><?= rex_i18n::msg('multinewsletter_menu_versand') ?></div></header>
			<div class="panel-body">
				<fieldset>
					<legend><?= rex_i18n::msg('multinewsletter_newsletter_send_step1') ?></legend>
					<?php
                        if($session_multinewsletter['newsletter']['status'] > 0) {
                            $article = rex_article::get($session_multinewsletter['newsletter']['article_id']);
                    ?>
					<dl class="rex-form-group form-group">
						<dt><label for="article_link"><?= rex_i18n::msg('multinewsletter_newsletter_article') ?></label></dt>
						<dd><a href="<?= rex::getServer() . rex_getUrl($session_multinewsletter['newsletter']['article_id'], 0) ?>" target="_blank">
									<?= $article instanceof rex_article ? $article->getName() : rex_i18n::msg('multinewsletter_newsletter_article') ?></a></dd>
					</dl>
					<dl class="rex-form-group form-group">
						<dt><label for="reset"></label></dt>
						<dd><input class="btn btn-delete" type="submit" name="reset" onclick="return myrex_confirm('<?= rex_i18n::msg('multinewsletter_confirm_reset') ?>',this.form)" value="<?= rex_i18n::msg('multinewsletter_button_cancelall') ?>" /></dd>
					</dl>
					<?php
                        } else {
                    ?>
					<dl class="rex-form-group form-group">
						<dt><label for="preselect_group"><?= rex_i18n::msg('multinewsletter_newsletter_load_group') ?></label></dt>
						<dd>
							<?php
                                $group_ids = new rex_select();
                                $group_ids->setSize(1);
                                $group_ids->setAttribute('class', 'form-control');
                                $group_ids->addOption(rex_i18n::msg('multinewsletter_newsletter_aus_einstellungen'), '0');
                                foreach($newsletter_groups as $group) {
                                    $group_ids->addOption($group->name, $group->id);
                                }
                                $group_ids->setSelected($session_multinewsletter['newsletter']['preselect_group']);
                                $group_ids->setAttribute('id', 'preselect_group');
                                $group_ids->setName('preselect_group');
                                echo $group_ids->get();

                                $sendernamen = [];
                                $clang_ids = []; // For JS some lines below
                                foreach(rex_clang::getAll() as $rex_clang) {
                                    $sendernamen[$rex_clang->getId()] = rex_config::get('multinewsletter', 'lang_'. $rex_clang->getId() .'_sendername');
                                    $clang_ids[$rex_clang->getId()] = $rex_clang->getCode();
                                }
                                $default_test_article = rex_article::get((int) rex_config::get('multinewsletter', 'default_test_article'));
                                $groups_default_settings = [
                                    0 => [
                                        'id' => '0',
                                        'name' => rex_i18n::msg('multinewsletter_newsletter_aus_einstellungen'),
                                        'default_sender_email' => rex_config::get('multinewsletter', 'sender'),
                                        'reply_to_email' => rex_config::get('multinewsletter', 'reply_to'),
                                        'default_article_id' => rex_config::get('multinewsletter', 'default_test_article'),
                                        'default_article_name' => $default_test_article instanceof rex_article ? $default_test_article->getName() : ''
                                    ]
                                ];
                            ?>
							<script>
								jQuery(document).ready(function($) {
									// presets
									var groupPresets = <?= json_encode(array_replace($groups_default_settings, $newsletter_groups)) ?>;
									var langs = <?= json_encode($clang_ids, JSON_FORCE_OBJECT) ?>;
									var einstellungenPresets = <?= json_encode($sendernamen, JSON_FORCE_OBJECT) ?>;
									$('#preselect_group').change(function(e) {
										var group_id = $(this).val();
										$('#REX_LINK_1').val(groupPresets[group_id]['default_article_id']);
										$('#REX_LINK_1_NAME').val(groupPresets[group_id]['default_article_name']);
										$('[name="sender_email"]').val(groupPresets[group_id]['default_sender_email']);
										$('[name="reply_to_email"]').val(groupPresets[group_id]['reply_to_email']);
										var index;
										for (index in langs) {
											if(group_id === "0") {
												$('[name="sender_name[' + index + ']"]').val(einstellungenPresets[index]);
											}
											else {
												$('[name="sender_name[' + index + ']"]').val(groupPresets[group_id]['default_sender_name']);
											}
										}
									});
								});
							</script>
						</dd>
					</dl>
					<?php
                            \TobiasKrais\D2UHelper\BackendHelper::form_linkfield('multinewsletter_newsletter_article', '1', $session_multinewsletter['newsletter']['article_id'], $session_multinewsletter['newsletter']['testlanguage']);
                            \TobiasKrais\D2UHelper\BackendHelper::form_input('multinewsletter_newsletter_email', 'sender_email', $session_multinewsletter['newsletter']['sender_email'], true, false, 'email');
                            \TobiasKrais\D2UHelper\BackendHelper::form_input('multinewsletter_config_reply_to', 'reply_to_email', $session_multinewsletter['newsletter']['reply_to_email'], false, false, 'email');
                            foreach(rex_clang::getAll() as $rex_clang) {
                                echo '<dl class="rex-form-group form-group">';
                                echo '<dt><label>'. rex_i18n::msg('multinewsletter_group_default_sender_name') .' '. $rex_clang->getName() .'</label></dt>';
                                echo '<dd><input class="form-control" type="text" name="sender_name['. $rex_clang->getId() .']" value="'. $session_multinewsletter['newsletter']['sender_name'][$rex_clang->getId()] .'" required /></dd>';
                                echo '</dl>';
                            }
                        }
                    ?>
				</fieldset>
				<fieldset>
					<legend><?= rex_i18n::msg('multinewsletter_newsletter_send_step2') ?></legend>
					<?php
                        if(0 === $session_multinewsletter['newsletter']['status']) {
                            $session_multinewsletter['newsletter']['groups'] = [];
                            $session_multinewsletter['newsletter']['man_recipients'] = [];
                            $session_multinewsletter['newsletter']['attachments'] = '';
                    ?>
					<dl class="rex-form-group form-group">
						<dt><label for="expl_testmail"></label></dt>
						<dd><?= rex_i18n::msg('multinewsletter_expl_testmail') ?></dd>
					</dl>
					<?php
                        \TobiasKrais\D2UHelper\BackendHelper::form_input('multinewsletter_newsletter_email', 'testemail', $session_multinewsletter['newsletter']['testemail'], true, false, 'email');

                        $options_anrede = [];
                        $options_anrede[-1] = rex_i18n::msg('multinewsletter_config_lang_title_without');
                        $options_anrede[0] = rex_i18n::msg('multinewsletter_config_lang_title_male');
                        $options_anrede[1] = rex_i18n::msg('multinewsletter_config_lang_title_female');
                        $options_anrede[2] = rex_i18n::msg('multinewsletter_config_lang_title_diverse');
                        \TobiasKrais\D2UHelper\BackendHelper::form_select('multinewsletter_newsletter_title', 'testtitle', $options_anrede, [$session_multinewsletter['newsletter']['testtitle']]);

                        \TobiasKrais\D2UHelper\BackendHelper::form_input('multinewsletter_newsletter_grad', 'testgrad', $session_multinewsletter['newsletter']['testgrad']);
                        \TobiasKrais\D2UHelper\BackendHelper::form_input('multinewsletter_newsletter_firstname', 'testfirstname', $session_multinewsletter['newsletter']['testfirstname']);
                        \TobiasKrais\D2UHelper\BackendHelper::form_input('multinewsletter_newsletter_lastname', 'testlastname', $session_multinewsletter['newsletter']['testlastname']);

                        if(count(rex_clang::getAll()) > 1) {
                            $langs = [];
                            foreach(rex_clang::getAll() as $rex_clang) {
                                $langs[$rex_clang->getId()] = $rex_clang->getName();
                            }
                            \TobiasKrais\D2UHelper\BackendHelper::form_select('multinewsletter_newsletter_clang', 'testlanguage', $langs, [$session_multinewsletter['newsletter']['testlanguage']]);
                        } else {
                            foreach(rex_clang::getAll() as $rex_clang) {
                                echo '<input type="hidden" name="testlanguage" value="'. $rex_clang->getId() .'" />';
                                break;
                            }
                        }
                        $attachments_html = rex_var_medialist::getWidget(1, 'attachments', $session_multinewsletter['newsletter']['attachments']);
                        echo '<dl class="rex-form-group form-group">';
                        echo '<dt><label>'. rex_i18n::msg('multinewsletter_attachments') .'</label></dt>';
                        echo '<dd>'. $attachments_html .'</dd>';
                        echo '</dl>';
                    ?>
					<dl class="rex-form-group form-group">
						<dt><label for="sendtestmail"></label></dt>
						<dd><input class="btn btn-save" type="submit" name="sendtestmail" value="<?= rex_i18n::msg('multinewsletter_newsletter_sendtestmail') ?>" /></dd>
					</dl>
					<?php
                        } // ENDIF STATUS = 0
                        else {
                    ?>
					<dl class="rex-form-group form-group">
						<dt><label for="sendtestmail_again"></label></dt>
						<dd><a href="javascript:location.reload()"><button class="btn btn-save" type="submit" name="sendtestmail" value="<?= rex_i18n::msg('multinewsletter_newsletter_sendtestmail') ?>"><?= rex_i18n::msg('multinewsletter_newsletter_testmailagain') ?></button></a></dd>
					</dl>
					<?php
                        }
                    ?>
				</fieldset>
				<fieldset>
					<legend><?= rex_i18n::msg('multinewsletter_newsletter_send_step3') ?></legend>
					<?php
                        if(1 === (int) $session_multinewsletter['newsletter']['status']) {
                            $attachments_html = rex_var_medialist::getWidget(1, 'attachments', $session_multinewsletter['newsletter']['attachments']);
                            echo '<dl class="rex-form-group form-group">';
                            echo '<dt><label>'. rex_i18n::msg('multinewsletter_attachments') .'</label></dt>';
                            echo '<dd>'. $attachments_html .'</dd>';
                            echo '</dl>';
                    ?>
					<dl class="rex-form-group form-group">
						<dt><label for="expl_testmail"></label></dt>
						<dd><?= rex_i18n::msg('multinewsletter_expl_prepare') ?></dd>
					</dl>
					<dl class="rex-form-group form-group">
						<dt><label for="group[]"></label></dt>
						<dd>
							<?php
                                $select = new rex_select();
                                $select->setSize(5);
                                $select->setMultiple(true);
                                $select->setName('group[]');
                                foreach($newsletter_groups as $group) {
                                    $select->addOption($group->name, $group->id);
                                }
                                $select->setSelected($session_multinewsletter['newsletter']['groups']);
                                $select->setAttribute('class', 'form-control');
                                $select->show();
                            ?>
						</dd>
					</dl>
                    <?php if (1 === (int) rex_config::get('multinewsletter', 'allow_recipient_selection')): ?>
                        <dl class="rex-form-group form-group">
                            <dt></dt>
                            <dd><?= rex_i18n::msg('multinewsletter_recipient_selection') ?></dd>
                        </dl>
                        <dl class="rex-form-group form-group">
                            <dt><label for="group[]"></label></dt>
                            <dd>
                                <?php
                                    $users = MultinewsletterUserList::getAll();
                                    $select = new rex_select();
                                    $select->setSize(15);
                                    $select->setMultiple(true);
                                    $select->setName('recipients[]');
                                    foreach($users as $user) {
                                        $select->addOption($user->getName() .' [ '. $user->email .' ]', $user->id);
                                    }
                                    $select->setSelected($session_multinewsletter['newsletter']['man_recipients']);
                                    $select->setAttribute('class', 'form-control select2');
                                    $select->show();
                                ?>
                            </dd>
                        </dl>
                    <?php endif ?>

					<dl class="rex-form-group form-group">
						<dt><label for="prepare"></label></dt>
						<dd><input class="btn btn-save" type="submit" name="prepare" onclick="return myrex_confirm(\' <?= rex_i18n::msg('multinewsletter_confirm_prepare') ?> \',this.form)" value="<?= rex_i18n::msg('multinewsletter_newsletter_prepare') ?>" /></dd>
					</dl>
					<?php
                        } // ENDIF STATUS==1
                        elseif(2 === (int) $session_multinewsletter['newsletter']['status']) {
                            // Leerzeile
                        }
                    ?>
				</fieldset>
				<fieldset id="newsletter-submit-fieldset">
					<legend><?= rex_i18n::msg('multinewsletter_newsletter_send_step4') ?></legend>
					<?php
                        if((2 === (int) $session_multinewsletter['newsletter']['status'] || 3 === (int) $session_multinewsletter['newsletter']['status']) && $newsletterManager->countRemainingUsers() > 0) {
                    ?>
					<dl class="rex-form-group form-group">
						<dt><label for="expl_send"></label></dt>
						<dd>
						<?php
                            echo '<p>'. rex_i18n::msg('multinewsletter_expl_send') .'</p>';
                            echo '<p>'. rex_i18n::msg('multinewsletter_newsletter_2send', $newsletterManager->countRemainingUsers()) .'</p>';
                            if('' !== rex_request::get('send', 'string')) {
                                echo '<br /><p id="newsletter_reloadinp">'. rex_i18n::rawMsg('multinewsletter_newsletter_reloadin')
                                    .'<br />(<a href="javascript:void(0)" onclick="stopreload()">'.
                                    rex_i18n::msg('multinewsletter_newsletter_stop_reload') .'</a>)</p>';

                                // get an array of users that should receive the newsletter
                                $limit_left = $newsletterManager->countRemainingUsers() % (rex_config::get('multinewsletter', 'versandschritte_nacheinander') * rex_config::get('multinewsletter', 'max_mails'));
                                $seconds_to_reload = 3;
                                if(0 === $limit_left) {
                                    $seconds_to_reload = rex_config::get('multinewsletter', 'sekunden_pause');
                                }
                        ?>
								<script>
									var time_left = <?= (string) $seconds_to_reload ?>,
										$fieldset = $('#newsletter-submit-fieldset'),
										$reloadin = $fieldset.find('#newsletter_reloadin');

									$reloadin.html(time_left);

									function countdownreload() {
										$reloadin.html(time_left);
										if(time_left > 0) {
											active = window.setTimeout("countdownreload()", 1000);
										}
										else {
											reload();
										}
										time_left = time_left - 1;
									}

									function reload() {
										$reloadin.html(0);
										$fieldset.find('input[name=send]').trigger('click');
									}

									function stopreload() {
										window.clearTimeout(active);
										$fieldset.find('#newsletter_reloadinp').html('');
									}

									active = window.setTimeout("countdownreload()", 3000);
								</script>
						<?php
                            }
                        ?>
						</dd>
					</dl>
					<dl class="rex-form-group form-group">
						<dt><label for="send"></label></dt>
						<dd>
							<input class="btn btn-save" type="submit" name="send" value="<?= rex_i18n::msg('multinewsletter_newsletter_send') ?>" />
							<?php
                                if(rex_addon::get('cronjob')->isAvailable() && 'active' === rex_config::get('multinewsletter', 'autosend', 'inactive') && '' !== rex_config::get('multinewsletter', 'admin_email', '')) {
                                    echo '<input class="btn btn-save" type="submit" name="send_cron" value="'. rex_i18n::msg('multinewsletter_newsletter_send_cron') .'" />';
                                }
                            ?>
						</dd>
					</dl>
				<?php
                    } // ENDIF STATUS==3
                    elseif((2 === (int) $session_multinewsletter['newsletter']['status'] || 3 === (int) $session_multinewsletter['newsletter']['status']) && 0 === $newsletterManager->countRemainingUsers()) {
                        // Damit beim nächsten Aufruf der Seite wieder von vorn losgelegt werden kann
                        $session_multinewsletter['newsletter']['status'] = 0;
                ?>
					<dl class="rex-form-group form-group">
						<dt><label for="sent"></label></dt>
						<dd><?= rex_i18n::msg('multinewsletter_newsletter_sent') ?></dd>
					</dl>
				<?php
                    }
                ?>
				</fieldset>
			</div>
		</div>
	</form>
<?php
} // if(class_exists("rex_mailer"))

rex_request::setSession('multinewsletter', $session_multinewsletter);