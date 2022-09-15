<?php
$newsletterManager = new MultinewsletterNewsletterManager($this->getConfig('max_mails'));
// First do reset action
if(filter_input(INPUT_POST, 'reset') != "") {
	// 0 = reset complete sendlist
	$newsletterManager->reset(filter_input(INPUT_POST, 'reset', FILTER_VALIDATE_INT, ['options' => ['default'=>0]]));
}

// Autosend stuff
if(!rex_addon::get('cronjob')->isAvailable() || rex_config::get('multinewsletter', 'autosend', 'inactive') != 'active' || rex_config::get('multinewsletter', 'admin_email', '') == '') {
	// If autosend is not correctly configured
	print rex_view::warning(rex_i18n::msg('multinewsletter_newsletter_send_cron_not_available'));
}
else {
	$autosend_message = '';
	// if automatic send in background is requested
	if(filter_input(INPUT_POST, 'send_cron') != "") {
		// Send in background via CronJob
		foreach($newsletterManager->archives as $archive) {
			$archive->setAutosend();
		}
		$autosend_message = '<p>'. rex_i18n::msg('multinewsletter_newsletter_send_cron_active') .'</p><br>';
		// Reset send settings
		unset($_SESSION['multinewsletter']);
	}
	else {
		// Autosend status message if autosend is active
		$newsletterManager_autosend = new MultinewsletterNewsletterManager($this->getConfig('max_mails'), TRUE);
		if($newsletterManager_autosend->countRemainingUsers() > 0) {
			$autosend_message = '<p>'. rex_i18n::msg('multinewsletter_newsletter_send_cron_warning') .'</p><br>';
		}
	}
	if($autosend_message) {
		// Detailed newsletter information
		$newsletterManager_autosend = new MultinewsletterNewsletterManager($this->getConfig('max_mails'), TRUE);
		$autosend_message .= '<form action="'. rex_url::currentBackendPage() .'" method="post" name="multinewsletter-cron-abort"><ul>';
		foreach($newsletterManager_autosend->archives as $autosend_archive) {
			$autosend_message .= '<li>'. $autosend_archive->countRemainingUsers() .' '. rex_i18n::msg('multinewsletter_archive_recipients') .': '. $autosend_archive->subject .' ('. $autosend_archive->sender_name.') <button class="btn btn-delete" style="margin: 5px 15px;" type="submit" name="reset" value="'. $autosend_archive->id .'">'. rex_i18n::msg('multinewsletter_newsletter_send_cron_abort') .'</button></li>';
		}
		$autosend_message .= '</ul></form>';
		print rex_view::warning($autosend_message);
	}
}

$messages = [];

// Suchkriterien in Session schreiben
if(!isset($_SESSION['multinewsletter'])) {
	$_SESSION['multinewsletter'] = [];
}
if(!isset($_SESSION['multinewsletter']['newsletter'])) {
	$_SESSION['multinewsletter']['newsletter'] = [];
}
if(!isset($_SESSION['multinewsletter']['newsletter']['sender_name'])) {
	$_SESSION['multinewsletter']['newsletter']['sender_name'] = [];
}

// Vorauswahl der Gruppe
if(filter_input(INPUT_POST, 'preselect_group', FILTER_VALIDATE_INT, ['options' => ['default'=> 0]]) > 0) {
	$_SESSION['multinewsletter']['newsletter']['preselect_group'] = filter_input(INPUT_POST, 'preselect_group', FILTER_VALIDATE_INT);
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['preselect_group'])
	|| $_SESSION['multinewsletter']['newsletter']['preselect_group'] < 0
	|| $_SESSION['multinewsletter']['newsletter']['preselect_group'] == "") {
	$_SESSION['multinewsletter']['newsletter']['preselect_group'] = 0;
}

// Status des Sendefortschritts. Bedeutungen
if(!isset($_SESSION['multinewsletter']['newsletter']['status']) && $newsletterManager->countRemainingUsers() == 0) {
	// 0 = Aufruf des neuen Formulars
	$_SESSION['multinewsletter']['newsletter']['status'] = 0;
}
else if(filter_input(INPUT_POST, 'reset') != "") {
	$_SESSION['multinewsletter']['newsletter']['status'] = 0;
}
else if(filter_input(INPUT_POST, 'sendtestmail') != "") {
	// 1 = Testmail wurde verschickt
	// Status wird säter nur gesetzt, wenn kein Fehler beim Versand auftrat
}
else if(filter_input(INPUT_POST, 'prepare') != "") {
	// 2 = Benutzer wurden vorbereitet
	// Status wird säter nur gesetzt, wenn kein Fehler beim Vorbereiten auftrat
}
else if(filter_input(INPUT_POST, 'send') != "" || $newsletterManager->countRemainingUsers() > 0) {
	// 3 = Versand gestartet
	$_SESSION['multinewsletter']['newsletter']['status'] = 3;
}

// Ausgewählter Artikel
$form_link = filter_input_array(INPUT_POST, array('REX_INPUT_LINK'=> array('filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY)));
if(!empty($form_link['REX_INPUT_LINK'])) {
	$_SESSION['multinewsletter']['newsletter']['article_id'] = $form_link['REX_INPUT_LINK'][1];
	$link_names = filter_input_array(INPUT_POST, array('REX_LINK_NAME' => array('flags' => FILTER_REQUIRE_ARRAY)));
	$_SESSION['multinewsletter']['newsletter']['article_name'] = $link_names['REX_LINK_NAME'][1];
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['article_id'])) {
	if($newsletterManager->countRemainingUsers() > 0) {
		// If there is a non-autosend archive, take article name from archive, article ID is not relevant
		$archives = MultinewsletterNewsletterManager::getArchivesToSend(true);
		$_SESSION['multinewsletter']['newsletter']['article_id'] = 0;
		$_SESSION['multinewsletter']['newsletter']['article_name'] = $archives[0]->subject;
	}
	else {
		// Otherwise take article and and article ID from settings
		$_SESSION['multinewsletter']['newsletter']['article_id'] = $this->getConfig('default_test_article');
		$_SESSION['multinewsletter']['newsletter']['article_name'] = $this->getConfig('default_test_article_name');
	}
}

// Ausgewählter Sender E-Mail
if(filter_input(INPUT_POST, 'sender_email') != "") {
	$_SESSION['multinewsletter']['newsletter']['sender_email'] = filter_input(INPUT_POST, 'sender_email', FILTER_VALIDATE_EMAIL);
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['sender_email'])) {
	$_SESSION['multinewsletter']['newsletter']['sender_email'] = $this->getConfig('sender');
}

// Ausgewählter Sender Name
$form_sendernamen = filter_input_array(INPUT_POST, array('sender_name'=> array('flags' => FILTER_REQUIRE_ARRAY)));
foreach(rex_clang::getAll() as $rex_clang) {
	if(isset($form_sendernamen['sender_name'][$rex_clang->getId()])) {
		$_SESSION['multinewsletter']['newsletter']['sender_name'][$rex_clang->getId()] = $form_sendernamen['sender_name'][$rex_clang->getId()];
	}
	else if(!isset($_SESSION['multinewsletter']['newsletter']['sender_name'][$rex_clang->getId()])) {
		if($this->hasConfig('lang_'. $rex_clang->getId() .'_sendername')) {
			$_SESSION['multinewsletter']['newsletter']['sender_name'][$rex_clang->getId()] = $this->getConfig('lang_'. $rex_clang->getId() .'_sendername');
		}
		else {
			$_SESSION['multinewsletter']['newsletter']['sender_name'][$rex_clang->getId()] = "";
		}
	}
}

// Reply-to E-Mail
if(filter_input(INPUT_POST, 'reply_to_email') != "") {
	$_SESSION['multinewsletter']['newsletter']['reply_to_email'] = filter_input(INPUT_POST, 'reply_to_email', FILTER_VALIDATE_EMAIL);
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['reply_to_email'])) {
	$_SESSION['multinewsletter']['newsletter']['reply_to_email'] = $this->getConfig('reply_to');
}

// Testmail Empfäger E-Mail
if(filter_input(INPUT_POST, 'testemail') != "") {
	$_SESSION['multinewsletter']['newsletter']['testemail'] = filter_input(INPUT_POST, 'testemail', FILTER_VALIDATE_EMAIL);
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['testemail'])) {
	$_SESSION['multinewsletter']['newsletter']['testemail'] = $this->getConfig('default_test_email');
}

// Testmail Empfäger Titel
if(filter_input(INPUT_POST, 'testtitle') != "") {
	$_SESSION['multinewsletter']['newsletter']['testtitle'] = filter_input(INPUT_POST, 'testtitle', FILTER_VALIDATE_INT);
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['testtitle'])) {
	$_SESSION['multinewsletter']['newsletter']['testtitle'] = $this->getConfig('default_test_anrede');
}

// Testmail Empfäger Akademischer Grad
if(filter_input(INPUT_POST, 'testgrad') != "") {
	$_SESSION['multinewsletter']['newsletter']['testgrad'] = filter_input(INPUT_POST, 'testgrad');
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['testgrad'])) {
	$_SESSION['multinewsletter']['newsletter']['testgrad'] = "";
}

// Testmail Empfäger Vorname
if(filter_input(INPUT_POST, 'testfirstname') != "") {
	$_SESSION['multinewsletter']['newsletter']['testfirstname'] = filter_input(INPUT_POST, 'testfirstname');
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['testfirstname'])) {
	$_SESSION['multinewsletter']['newsletter']['testfirstname'] = $this->getConfig('default_test_vorname');
}

// Testmail Empfäger Nachname
if(filter_input(INPUT_POST, 'testlastname') != "") {
	$_SESSION['multinewsletter']['newsletter']['testlastname'] = filter_input(INPUT_POST, 'testlastname');
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['testlastname'])) {
	$_SESSION['multinewsletter']['newsletter']['testlastname'] = $this->getConfig('default_test_nachname');
}

// Testmail Empfäger Sprache
if(filter_input(INPUT_POST, 'testlanguage') != "") {
	$_SESSION['multinewsletter']['newsletter']['testlanguage'] = filter_input(INPUT_POST, 'testlanguage', FILTER_VALIDATE_INT);
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['testlanguage'])) {
	$_SESSION['multinewsletter']['newsletter']['testlanguage'] = $this->getConfig('default_test_sprache');
}

// Für den Versand ausgewählte Gruppen
$form_groups = filter_input_array(INPUT_POST, array('group'=> array('filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY)));
if(!empty($form_groups['group'])) {
	$_SESSION['multinewsletter']['newsletter']['groups'] = $form_groups['group'];
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['groups']) || !is_array($_SESSION['multinewsletter']['newsletter']['groups'])) {
	$_SESSION['multinewsletter']['newsletter']['groups'] = array($_SESSION['multinewsletter']['newsletter']['preselect_group']);
}

// Attachments
$attachments = trim(rex_post('attachments', 'string'));
if(strlen($attachments)) {
	$_SESSION['multinewsletter']['newsletter']['attachments'] = $attachments;
}
else if((!isset($_SESSION['multinewsletter']['newsletter']['attachments']) || strlen($_SESSION['multinewsletter']['newsletter']['attachments']) == 0) && $_SESSION['multinewsletter']['newsletter']['article_id'] > 0 && rex_article::get($_SESSION['multinewsletter']['newsletter']['article_id'])) {
    $_SESSION['multinewsletter']['newsletter']['attachments'] = rex_article::get($_SESSION['multinewsletter']['newsletter']['article_id'])->getValue('art_newsletter_attachments');
}

// Für den Versand ausgewählte Empfänger
$recipients = array_filter(rex_post('recipients', 'array', []));
if(count($recipients)) {
	$_SESSION['multinewsletter']['newsletter']['man_recipients'] = $recipients;
}

// Die Gruppen laden
$newsletter_groups = MultinewsletterGroup::getAll();

$time_started = time();
$maxtimeout = ini_get('max_execution_time');
if($maxtimeout == 0) {
	$maxtimeout = 20;
}

// Send test mail
if(filter_input(INPUT_POST, 'sendtestmail') != "") {
	// Exists article and is it online
	if(intval($_SESSION['multinewsletter']['newsletter']['article_id']) <= 0) {
		$messages[] = rex_i18n::msg('multinewsletter_error_noarticle');
	}
	else {
		$temp = rex_article::get(
			$_SESSION['multinewsletter']['newsletter']['article_id'],
			$_SESSION['multinewsletter']['newsletter']['testlanguage']
		);
		if(!is_object($temp) || !$temp->isOnline()) {
			$messages[] = rex_i18n::msg('multinewsletter_error_articlenotfound',
				$_SESSION['multinewsletter']['newsletter']['article_id'],
				rex_clang::get($_SESSION['multinewsletter']['newsletter']['testlanguage'])->getName());
		}
		unset($temp);
	}

	// Send
	if(filter_var($_SESSION['multinewsletter']['newsletter']['testemail'], FILTER_SANITIZE_EMAIL) === false) {
		$messages[] = rex_i18n::msg('multinewsletter_error_invalidemail',
			$_SESSION['multinewsletter']['newsletter']['testemail']);
	}

	if(empty($messages)) {
		$testnewsletter = MultinewsletterNewsletter::factory($_SESSION['multinewsletter']['newsletter']['article_id'],
			$_SESSION['multinewsletter']['newsletter']['testlanguage']);

		$testuser = MultinewsletterUser::factory($_SESSION['multinewsletter']['newsletter']['testemail'],
			$_SESSION['multinewsletter']['newsletter']['testtitle'],
			$_SESSION['multinewsletter']['newsletter']['testgrad'],
			$_SESSION['multinewsletter']['newsletter']['testfirstname'],
			$_SESSION['multinewsletter']['newsletter']['testlastname'],
			$_SESSION['multinewsletter']['newsletter']['testlanguage']);

		$testnewsletter->sender_email = $_SESSION['multinewsletter']['newsletter']['sender_email'];
		$testnewsletter->sender_name = $_SESSION['multinewsletter']['newsletter']['sender_name'][$_SESSION['multinewsletter']['newsletter']['testlanguage']];
		$testnewsletter->reply_to_email = $_SESSION['multinewsletter']['newsletter']['reply_to_email'];
		$sendresult = $testnewsletter->sendTestmail($testuser, $_SESSION['multinewsletter']['newsletter']['article_id']);

		if(!$sendresult) {
			$messages[] = rex_i18n::msg('multinewsletter_error_senderror');
		}
		else {
			$_SESSION['multinewsletter']['newsletter']['status'] = 1;
		}
	}
}
// Adressen vorbereiten
else if(filter_input(INPUT_POST, 'prepare') != "") {
	if(count($_SESSION['multinewsletter']['newsletter']['groups']) == 0 && count($_SESSION['multinewsletter']['newsletter']['man_recipients']) == 0) {
		$messages[] = rex_i18n::msg('multinewsletter_error_nogroupselected');
	}

	if(empty($messages)) {
		$offline_lang_ids = $newsletterManager->prepare($_SESSION['multinewsletter']['newsletter']['groups'],
			$_SESSION['multinewsletter']['newsletter']['article_id'],
			MultinewsletterNewsletter::getFallbackLang(),
            $_SESSION['multinewsletter']['newsletter']['man_recipients'],
            $_SESSION['multinewsletter']['newsletter']['attachments']);

		if(count($offline_lang_ids) > 0) {
			$offline_langs = [];
			foreach($offline_lang_ids as $clang_id) {
				$offline_langs[] = rex_clang::get($clang_id)->getName();
			}
			if(is_null(MultinewsletterNewsletter::getFallbackLang()) || in_array(MultinewsletterNewsletter::getFallbackLang(), $offline_lang_ids)) {
				$messages[] = rex_i18n::msg('multinewsletter_error_someclangsoffline', implode(", ", $offline_langs));
			}
			else {
				$messages[] = rex_i18n::msg('multinewsletter_error_someclangsdefault', implode(", ", $offline_langs));
			}
		}
		$_SESSION['multinewsletter']['newsletter']['status'] = 2;
	}
}
// Versand des Newsletters
else if(filter_input(INPUT_POST, 'send') != "") {
	$number_mails_send = $newsletterManager->countRemainingUsers() % $this->getConfig('max_mails');
	if($number_mails_send == 0) {
		$number_mails_send = $this->getConfig('max_mails');
	}
	$sendresult = $newsletterManager->send($number_mails_send);
	if($sendresult !== TRUE) {
		$messages[] = rex_i18n::msg('multinewsletter_error_send_incorrect_user') .' '. implode(", ", $sendresult);
	}
	if(count($newsletterManager->last_send_users) > 0) {
		$message = rex_i18n::msg('multinewsletter_expl_send_success').'<br /><ul>';
		foreach($newsletterManager->last_send_users as $user) {
			$message .= "<li>";
			if($user->firstname != "" || $user->lastname != "") {
				$message .= $user->firstname ." ". $user->lastname .": ";
			}
			$message .= $user->email ."</li>";
		}
		$message .= "</ul>";
		echo rex_view::success($message);
	}
	$_SESSION['multinewsletter']['newsletter']['status'] = 3;
}

// Fehler ausgeben
foreach($messages as $msg) {
	echo rex_view::error($msg);
}

if(class_exists("rex_mailer")) {
?>
	<form action="<?php print rex_url::currentBackendPage(); ?>" method="post" name="MULTINEWSLETTER">
		<div class="panel panel-edit">
			<header class="panel-heading"><div class="panel-title"><?php print rex_i18n::msg('multinewsletter_menu_versand'); ?></div></header>
			<div class="panel-body">
				<fieldset>
					<legend><?php print rex_i18n::msg('multinewsletter_newsletter_send_step1'); ?></legend>
					<?php
						if($_SESSION['multinewsletter']['newsletter']['status'] > 0) {
					?>
					<dl class="rex-form-group form-group">
						<dt><label for="article_link"><?php print rex_i18n::msg('multinewsletter_newsletter_article'); ?></label></dt>
						<dd><a href="<?php print rex::getServer() . rex_getUrl($_SESSION['multinewsletter']['newsletter']['article_id'], 0); ?>" target="_blank">
									<?php print $_SESSION['multinewsletter']['newsletter']['article_name']?></a></dd>
					</dl>
					<dl class="rex-form-group form-group">
						<dt><label for="reset"></label></dt>
						<dd><input class="btn btn-delete" type="submit" name="reset" onclick="return myrex_confirm('<?php print rex_i18n::msg('multinewsletter_confirm_reset'); ?>',this.form)" value="<?php print rex_i18n::msg('multinewsletter_button_cancelall'); ?>" /></dd>
					</dl>
					<?php
						}
						else {
					?>
					<dl class="rex-form-group form-group">
						<dt><label for="preselect_group"><?php print rex_i18n::msg('multinewsletter_newsletter_load_group'); ?></label></dt>
						<dd>
							<?php
								$group_ids = new rex_select();
								$group_ids->setSize(1);
								$group_ids->setAttribute('class', 'form-control');
								$group_ids->addOption(rex_i18n::msg('multinewsletter_newsletter_aus_einstellungen'),'0');
								foreach($newsletter_groups as $group) {
									$group_ids->addOption($group->name, $group->id);
								}
								$group_ids->setSelected($_SESSION['multinewsletter']['newsletter']['preselect_group']);
								$group_ids->setAttribute('id', 'preselect_group');
								$group_ids->setName('preselect_group');
								print $group_ids->get();

								$sendernamen = [];
								$clang_ids = []; // For JS some lines below
								foreach(rex_clang::getAll() as $rex_clang) {
									$sendernamen[$rex_clang->getId()] = $this->getConfig('lang_'. $rex_clang->getId() .'_sendername');
									$clang_ids[$rex_clang->getId()] = $rex_clang->getCode();
								}
								$groups_default_settings[0] = [
									'id' => '0',
									'name' => rex_i18n::msg('multinewsletter_newsletter_aus_einstellungen'),
									'default_sender_email' => $this->getConfig('sender'),
									'reply_to_email' => $this->getConfig('reply_to'),
									'default_article_id' => $this->getConfig('default_test_article'),
									'default_article_name' => $this->getConfig('default_test_article_name'),
								];
							?>
							<script>
								jQuery(document).ready(function($) {
									// presets
									var groupPresets = <?php echo json_encode(array_replace($groups_default_settings, $newsletter_groups)); ?>;
									var langs = <?php echo json_encode($clang_ids, JSON_FORCE_OBJECT); ?>;
									var einstellungenPresets = <?php echo json_encode($sendernamen, JSON_FORCE_OBJECT); ?>;
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
							d2u_addon_backend_helper::form_linkfield('multinewsletter_newsletter_article', 1, $_SESSION['multinewsletter']['newsletter']['article_id'], $_SESSION['multinewsletter']['newsletter']['testlanguage']);
							d2u_addon_backend_helper::form_input('multinewsletter_newsletter_email', 'sender_email', $_SESSION['multinewsletter']['newsletter']['sender_email'], TRUE, FALSE, 'email');
							d2u_addon_backend_helper::form_input('multinewsletter_config_reply_to', 'reply_to_email', $_SESSION['multinewsletter']['newsletter']['reply_to_email'], FALSE, FALSE, 'email');
							foreach(rex_clang::getAll() as $rex_clang) {
								print '<dl class="rex-form-group form-group">';
								print '<dt><label>'. rex_i18n::msg('multinewsletter_group_default_sender_name') .' '. $rex_clang->getName() .'</label></dt>';
								print '<dd><input class="form-control" type="text" name="sender_name['. $rex_clang->getId() .']" value="'. $_SESSION['multinewsletter']['newsletter']['sender_name'][$rex_clang->getId()] .'" required /></dd>';
								print '</dl>';
							}
						}
					?>
				</fieldset>
				<fieldset>
					<legend><?php print rex_i18n::msg('multinewsletter_newsletter_send_step2'); ?></legend>
					<?php
						if($_SESSION['multinewsletter']['newsletter']['status'] == 0) {
                            $_SESSION['multinewsletter']['newsletter']['groups'] = [];
                            $_SESSION['multinewsletter']['newsletter']['man_recipients'] = [];
                            $_SESSION['multinewsletter']['newsletter']['attachments'] = '';
					?>
					<dl class="rex-form-group form-group">
						<dt><label for="expl_testmail"></label></dt>
						<dd><?php print rex_i18n::msg('multinewsletter_expl_testmail'); ?></dd>
					</dl>
					<?php
						d2u_addon_backend_helper::form_input('multinewsletter_newsletter_email', "testemail", $_SESSION['multinewsletter']['newsletter']['testemail'], TRUE, FALSE, 'email');

						$options_anrede = [];
						$options_anrede[0] = rex_i18n::msg('multinewsletter_config_lang_title_male');
						$options_anrede[1] = rex_i18n::msg('multinewsletter_config_lang_title_female');
						d2u_addon_backend_helper::form_select('multinewsletter_newsletter_title', 'testtitle', $options_anrede, [$_SESSION['multinewsletter']['newsletter']['testtitle']]);

						d2u_addon_backend_helper::form_input('multinewsletter_newsletter_grad', "testgrad", $_SESSION['multinewsletter']['newsletter']['testgrad']);
						d2u_addon_backend_helper::form_input('multinewsletter_newsletter_firstname', "testfirstname", $_SESSION['multinewsletter']['newsletter']['testfirstname']);
						d2u_addon_backend_helper::form_input('multinewsletter_newsletter_lastname', "testlastname", $_SESSION['multinewsletter']['newsletter']['testlastname']);

						if(count(rex_clang::getAll()) > 1) {
							$langs = [];
							foreach(rex_clang::getAll() as $rex_clang) {
								$langs[$rex_clang->getId()] = $rex_clang->getName();
							}
							d2u_addon_backend_helper::form_select('multinewsletter_newsletter_clang', 'testlanguage', $langs, [$_SESSION['multinewsletter']['newsletter']['testlanguage']]);
						}
						else {
							foreach(rex_clang::getAll() as $rex_clang) {
								echo '<input type="hidden" name="testlanguage" value="'. $rex_clang->getId() .'" />';
								break;
							}
						}
					?>
					<dl class="rex-form-group form-group">
						<dt><label for="sendtestmail"></label></dt>
						<dd><input class="btn btn-save" type="submit" name="sendtestmail" value="<?php print rex_i18n::msg('multinewsletter_newsletter_sendtestmail'); ?>" /></dd>
					</dl>
					<?php
						} // ENDIF STATUS = 0
						else {
					?>
					<dl class="rex-form-group form-group">
						<dt><label for="sendtestmail_again"></label></dt>
						<dd><a href="javascript:location.reload()"><button class="btn btn-save" type="submit" name="sendtestmail" value="<?php print rex_i18n::msg('multinewsletter_newsletter_sendtestmail'); ?>"><?php print rex_i18n::msg('multinewsletter_newsletter_testmailagain'); ?></button></a></dd>
					</dl>
					<?php
						}
					?>
				</fieldset>
				<fieldset>
					<legend><?php print rex_i18n::msg('multinewsletter_newsletter_send_step3'); ?></legend>
					<?php
						if($_SESSION['multinewsletter']['newsletter']['status'] == 1) {
                            $attachments_html = rex_var_medialist::getWidget(1, 'attachments', $_SESSION['multinewsletter']['newsletter']['attachments']);
                            print '<dl class="rex-form-group form-group">';
                            print '<dt><label>'. rex_i18n::msg('multinewsletter_attachments') .'</label></dt>';
                            print '<dd>'. $attachments_html .'</dd>';
                            print '</dl>';
					?>
					<dl class="rex-form-group form-group">
						<dt><label for="expl_testmail"></label></dt>
						<dd><?php print rex_i18n::msg('multinewsletter_expl_prepare'); ?></dd>
					</dl>
					<dl class="rex-form-group form-group">
						<dt><label for="group[]"></label></dt>
						<dd>
							<?php
								$select = new rex_select;
								$select->setSize(5);
								$select->setMultiple(1);
								$select->setName('group[]');
								foreach($newsletter_groups as $group) {
									$select->addOption($group->name, $group->id);
								}
								$select->setSelected($_SESSION['multinewsletter']['newsletter']['groups']);
								$select->setAttribute('class', 'form-control');
								$select->show();
							?>
						</dd>
					</dl>
                    <?php if ($this->getConfig('allow_recipient_selection')): ?>
                        <dl class="rex-form-group form-group">
                            <dt></dt>
                            <dd><?php print rex_i18n::msg('multinewsletter_recipient_selection'); ?></dd>
                        </dl>
                        <dl class="rex-form-group form-group">
                            <dt><label for="group[]"></label></dt>
                            <dd>
                                <?php
                                    $users = MultinewsletterUserList::getAll();
                                    $select = new rex_select;
                                    $select->setSize(15);
                                    $select->setMultiple(1);
                                    $select->setName('recipients[]');
                                    foreach($users as $user) {
                                        $select->addOption($user->getName() .' [ '. $user->email .' ]', $user->id);
                                    }
                                    $select->setSelected((array) $_SESSION['multinewsletter']['newsletter']['man_recipients']);
                                    $select->setAttribute('class', 'form-control select2');
                                    $select->show();
                                ?>
                            </dd>
                        </dl>
                    <?php endif; ?>

					<dl class="rex-form-group form-group">
						<dt><label for="prepare"></label></dt>
						<dd><input class="btn btn-save" type="submit" name="prepare" onclick="return myrex_confirm(\' <?php print rex_i18n::msg('multinewsletter_confirm_prepare'); ?> \',this.form)" value="<?php print rex_i18n::msg('multinewsletter_newsletter_prepare'); ?>" /></dd>
					</dl>
					<?php
						} // ENDIF STATUS==1
						else if($_SESSION['multinewsletter']['newsletter']['status'] == 2) {
							// Leerzeile
						}
					?>
				</fieldset>
				<fieldset id="newsletter-submit-fieldset">
					<legend><?php print rex_i18n::msg('multinewsletter_newsletter_send_step4'); ?></legend>
					<?php
						if(($_SESSION['multinewsletter']['newsletter']['status'] == 2 || $_SESSION['multinewsletter']['newsletter']['status'] == 3) && $newsletterManager->countRemainingUsers() > 0) {
					?>
					<dl class="rex-form-group form-group">
						<dt><label for="expl_send"></label></dt>
						<dd>
						<?php
							print '<p>'. rex_i18n::msg('multinewsletter_expl_send') .'</p>';
							print '<p>'. rex_i18n::msg('multinewsletter_newsletter_2send', $newsletterManager->countRemainingUsers()) .'</p>';
							if(filter_input(INPUT_POST, 'send') != "" && $newsletterManager->countRemainingUsers() > 0) {
								print '<br /><p id="newsletter_reloadinp">'. rex_i18n::rawMsg('multinewsletter_newsletter_reloadin')
									.'<br />(<a href="javascript:void(0)" onclick="stopreload()">'.
									rex_i18n::msg('multinewsletter_newsletter_stop_reload') .'</a>)</p>';

								// get an array of users that should receive the newsletter
								$limit_left = $newsletterManager->countRemainingUsers() % ($this->getConfig('versandschritte_nacheinander') * $this->getConfig('max_mails'));
								$seconds_to_reload = 3;
								if($limit_left == 0) {
									$seconds_to_reload = $this->getConfig('sekunden_pause');
								}
						?>
								<script>
									var time_left = <?php print $seconds_to_reload; ?>,
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
							<input class="btn btn-save" type="submit" name="send" value="<?php print rex_i18n::msg('multinewsletter_newsletter_send'); ?>" />
							<?php
								if(rex_addon::get('cronjob')->isAvailable() && rex_config::get('multinewsletter', 'autosend', 'inactive') == 'active' && rex_config::get('multinewsletter', 'admin_email', '') != '') {
									print '<input class="btn btn-save" type="submit" name="send_cron" value="'. rex_i18n::msg('multinewsletter_newsletter_send_cron') .'" />';
								}
							?>
						</dd>
					</dl>
				<?php
					} // ENDIF STATUS==3
					else if(($_SESSION['multinewsletter']['newsletter']['status'] == 2 || $_SESSION['multinewsletter']['newsletter']['status'] == 3) && $newsletterManager->countRemainingUsers() == 0) {
						// Damit beim nächsten Aufruf der Seite wieder von vorn losgelegt werden kann
						$_SESSION['multinewsletter']['newsletter']['status'] = 0;
				?>
					<dl class="rex-form-group form-group">
						<dt><label for="sent"></label></dt>
						<dd><?php print rex_i18n::msg('multinewsletter_newsletter_sent'); ?></dd>
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