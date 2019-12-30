<?php
$lang_presets = [
	[
		'code' => 'de',
		'language' => 'Deutsch',
		'sendername' => '',
		'anrede' => 'Anrede',
		'title_0' => 'Herr',
		'title_1' => 'Frau',
		'grad' => 'Titel',
		'firstname' => 'Vorname',
		'lastname' => 'Nachname',
		'email' => 'E-Mail',
		'select_newsletter' => 'Newsletter auswählen',
		'compulsory' => 'Felder mit einem * müssen ausgefüllt werden.',
		'privacy_policy' => "Ich willige ein dass ich per E-Mail über Produkte und Produktaktionen dieser Webseite informiert werde. Über den Umfang der Datenverarbeitung habe ich mich <a href='+++LINK_PRIVACY_POLICY+++'>hier</a> informiert.
Meine Daten werden ausschließlich zu diesem Zweck genutzt.
Eine Weitergabe der Daten an Dritte erfolgt nicht.
Ich kann die Einwilligung jederzeit unter den im <a href='+++LINK_IMPRESS+++'>Impressum</a> angegebenen Kontaktdaten oder durch Nutzung des in den E-Mails enthaltenen Abmeldelinks widerrufen.",
		'subscribe' => 'Newsletter abonnieren',
		'action' => 'Möchten Sie regelmäßig informiert werden? Dann abonnieren Sie unseren interessanten Newsletter:',
		'safety' => 'Bei der Newsletterbestellung erhalten Sie aus rechtlichen Gründen eine E-Mail mit einem Bestätigungslink.',
		'status1' => 'Ab sofort erhalten Sie unseren Newsletter.',
		'no_userdata' => 'Die eingegebenen Daten waren ungültig, bitte nochmal prüfen, ob alle Formularfelder korrekt ausgefüllt sind.',
		'invalid_email' => 'Die eingegebene E-Mailadresse ist nicht korrekt.',
		'invalid_firstname' => 'Bitte geben Sie Ihren Vornamen ein.',
		'invalid_lastname' => 'Bitte geben Sie Ihren Nachnamen ein.',
		'send_error' => 'Sendefehler - Überprüfen Sie die Einstellungen im PHP-Mailer-Addon.',
		'software_failure' => 'Es gab ein Software-Problem - bitte kontaktieren Sie den Webmaster!',
		'nogroup_selected' => 'Sie haben keinen Newsletter ausgewählt! Bitte wählen Sie, für welchen Newsletter Sie sich anmelden möchten!',
		'already_subscribed' => 'Sie sind bereits für unseren Newsletter eingetragen.',
		'confirmation_sent' => 'Ihnen wurde soeben eine Bestätigungs-E-Mail geschickt. Ihre Anfrage wird erst fertiggestellt, wenn Sie den in der E-Mail enthaltenen Link geklickt haben.',
		'confirmsubject' => 'Bitte bestätigen Sie Ihre Newsletter Anmeldung',
		'confirmcontent' => "<p>Lieber Newsletter Abonnent,</p>
<p>liebe Newsletter Abonnentin,</p><br>
<p>bitte bestätigen Sie aus rechtlichen Gründen Ihre Anmeldung zum Newsletter. Klicken Sie dazu einfach auf den folgenden Link:  <a href='+++AKTIVIERUNGSLINK+++'>+++AKTIVIERUNGSLINK+++</a></p><br>
<p>TIPP: Damit unsere E-Mails nicht ungewollt in den Spam-Ordner verschoben oder gelöscht werden, nehmen Sie uns einfach in Ihr persönliches Adressbuch auf.</p>
<p>Dazu klicken Sie je nach verwendetem E-Mail-Programm entweder</p>
<ul><li>auf den entsprechenden Adressbuch-Link neben der Absenderadresse oder</li><li>mit der rechten Maustaste oben auf die Absenderadresse und wählen im daraufhin erscheinenden Pop-up-Menü den Punkt „Zum Adressbuch hinzufügen“.</li></ul>
<p>So entgeht Ihnen garantiert keine Ausgabe unseres Newsletters!</p><br>
<p>Falls Sie den Newsletter nicht erhalten wollen, brauchen Sie nichts zu tun.</p><br>
<p>Vielen Dank,</p><br>
<p>Ihr Newsletter Team</p>",
		'already_confirmed' => 'Sie haben die letzte Aktion scheinbar schon bestätigt.',
		'invalid_key' => 'Der übermittelte Sicherheitscode war ungültig. Haben Sie eventuell einen neuen Code erhalten?',
		'confirmation_successful' => '<p>Vielen Dank für Ihre Bestätigung! Ihre E-Mail Adresse wurde angemeldet.</p>',
		'unsubscribe' => 'Newsletter abbestellen',
		'status0' => 'Sie erhalten ab sofort keinen Newsletter mehr von uns, können sich aber jederzeit wieder anmelden.',
		'already_unsubscribed' => 'Sie sind bereits für einen Newsletter ausgetragen.',
		'user_not_found' => 'Leider konnten wir Ihre Daten nicht in unserer Datenbank finden.',
	],
	[
		'code' => 'en',
		'language' => 'English',
		'sendername' => '',
		'anrede' => 'Form of address',
		'title_0' => 'Mr.',
		'title_1' => 'Mrs.',
		'grad' => 'Academic title',
		'firstname' => 'First name',
		'lastname' => 'Last name',
		'email' => 'E-Mail',
		'select_newsletter' => 'Select newsletter',
		'privacy_policy' => "I agree that I will be informed by e-mail about products and product promotions of this website. I’ve learned about the scope of data processing <a href='+++LINK_PRIVACY_POLICY+++'> here </a>.
My data is used exclusively for this purpose.
A transfer of the data to third parties does not take place.
I may withdraw your consent at any time from the contact information provided in the <a href='+++LINK_IMPRESS+++'> imprint </a> or by using the unsubscribe link contained in the emails.",
		'compulsory' => 'Fields marked * must be compulsorily filled.',
		'subscribe' => 'Subscribe newsletter',
		'action' => 'Do you want to be regularly informed? Then subscribe to our interesting newsletter:',
		'safety' => 'For legal reasons, you will receive an e-mail with a confirmation link. Your subscription will be completed after clicking the link.',
		'status1' => 'You will receive our newsletter from now on.',
		'no_userdata' => 'The entered data was invalid. Please recheck if all fields are filled in correct.',
		'invalid_email' => 'The e-mail address is invalid.',
		'invalid_firstname' => 'Please enter you first name.',
		'invalid_lastname' => 'Please enter your last name.',
		'send_error' => 'Send Failure - Please check PHP-Mailer-Addon settings.',
		'software_failure' => 'Software problem occured - please contact the Webmaster!',
		'nogroup_selected' => 'No newsletter selected. Please select which newsletter you want to subscribe!',
		'already_subscribed' => 'You already subscribed this newsletter.',
		'confirmation_sent' => 'We sent a confirmation mail to the submitted e-mail address. Your request will be completed by clicking on the link in the mail.',
		'confirmsubject' => 'Please confirm your registration',
		'confirmcontent' => "<p>Dear +++TITLE+++ +++GRAD+++ +++FIRSTNAME+++ +++LASTNAME+++,</p><br>
<p>for legal reasons – please confirm your newsletter registration by clicking on the following link: <a href='+++AKTIVIERUNGSLINK+++'>+++AKTIVIERUNGSLINK+++</a></p>
<p>HINT: Just to ensure that our E-mails are not inadvertently pushed to the spam folder or deleted, please simply add us in your personal address book.</p>
<p>For this, click on either of the following, depending on the used E-Mail program:</p>
<ul><li>on the corresponding address book link near the sender address or</li><li>right click the mouse over the sender address and on the displayed Pop-up menu, select the item „Add sender to address book“.</li></ul>
<p>This will ensure that you do not miss any edition of our Newsletters!</p>
<p>If you don’t want to receive this Newsletter, you don’t need to do anything. You will need to click on the link to confirm that you wish to subscribe.</p><br>
<p>Sincerely, yours</p><br>
<p>Newsletter-Team</p>",
		'already_confirmed' => 'You already entered the last action.',
		'invalid_key' => 'The submitted security code was invalid. Did you already receive a new code?',
		'confirmation_successful' => 'Your confirmation was successful.',
		'unsubscribe' => 'Unsubscribe Newsletter',
		'status0' => 'You successfully unsubscribed. You can subscribe again at any time.',
		'already_unsubscribed' => 'You already unsubscribed this newsletter.',
		'user_not_found' => 'Sorry, we are not able to find your data in our database.',
	],
	[
		'code' => 'it',
		'language' => 'Italiano',
		'sendername' => '',
		'anrede' => 'Titolo',
		'title_0' => 'Signor',
		'title_1' => 'Signora',
		'grad' => 'Titolo accademico',
		'firstname' => 'Nome',
		'lastname' => 'Cognome',
		'email' => 'E-Mail',
		'select_newsletter' => 'Scegli il newsletter',
		'privacy_policy' => "Accetto che sarò informato via e-mail sui prodotti e le promozioni dei prodotti di questo sito. Ho appreso lo scopo dell’elaborazione dei dati <a href='+++LINK_PRIVACY_POLICY+++'>qui</a>.
I miei dati sono usati esclusivamente per questo scopo.
Un trasferimento dei dati a terzi non ha luogo.
Posso ritirare il tuo consenso in qualsiasi momento dalle informazioni di contatto fornite nella <a href='+++LINK_IMPRESS+++'> impronta </a> o utilizzando il link di cancellazione contenuto nelle email.",
		'compulsory' => 'I campi * sono obbligatori.',
		'subscribe' => 'Abbonarsi',
		'action' => 'Vuole rimanere informato? Si iscrivi subito al nostro newsletter:',
		'safety' => 'Per motivi legali riceve una email di conferma. Sua registrazione sará completata quando clicca al Link nel email.',
		'status1' => 'Adesso si è abbonato al nostro newsletter.',
		'no_userdata' => 'I seguenti campi non sono stati compilati correttamente.',
		'invalid_email' => 'L\'indirizzo email non è valido.',
		'invalid_firstname' => 'Si prega di inserire il nome.',
		'invalid_lastname' => 'Si prega di inserire il cognome.',
		'send_error' => 'Errore di invio - Please check PHP-Mailer-Addon settings.',
		'software_failure' => 'Software problem occured - please contact the Webmaster!',
		'nogroup_selected' => 'Nessun newsletter scelto. Si prega di scegliere il newsletter preferito!',
		'already_subscribed' => 'È giá iscritto a questo newsletter.',
		'confirmation_sent' => 'Le abbiamo inviato un email di conferma. Sua registrazione sarà completata quando clicca al Link nel email.',
		'confirmsubject' => 'Confermare la registrazione',
		'confirmcontent' => "<p>Caro +++TITLE+++ +++GRAD+++ +++FIRSTNAME+++ +++LASTNAME+++,</p><br>
<p>per motivi legali, si prega di confermare il suo abbonamento al newsletter.<br/>Clicchi semplicemente al link seguente: <a href='+++AKTIVIERUNGSLINK+++'>+++AKTIVIERUNGSLINK+++</a></p>
<p>HINT: Just to ensure that our E-mails are not inadvertently pushed to the spam folder or deleted, please simply add us in your personal address book.</p>
<p>Così non Si perda nessun newsletter!</p>
<p>Se non vuole abbonarsi il newsletter, può ignorare questo email.</p><br>
<p>Grazie.</p><br>
<p>Newsletter-Team</p>",
		'already_confirmed' => 'Ha già confermato il Suo email..',
		'invalid_key' => 'Il codice di sicurezza non è valido. Probabilmente ha già ricevuto uno nuovo?',
		'confirmation_successful' => 'Sua registrazione è avvenuta con successo.',
		'unsubscribe' => 'Disdire l\'abbonamento newsletter',
		'status0' => 'Disdetta andata a buon fine. Si può riscrivere a qualsiasi momento.',
		'already_unsubscribed' => 'Non È più scritto a questo newsletter.',
		'user_not_found' => 'Ci dispiace, non l\'abbiamo trovato nella nostra banca dati.',
	],
];

// save settings
if (filter_input(INPUT_POST, "btn_save") == "Speichern") {
	$settings = (array) rex_post('settings', 'array', []);

	// Linkmap Link braucht besondere Behandlung
	$link_ids = filter_input_array(INPUT_POST, ['REX_INPUT_LINK'=> ['filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY]]);
	$link_names = filter_input_array(INPUT_POST, ['REX_LINK_NAME' => ['flags' => FILTER_REQUIRE_ARRAY]]);

	$settings['link'] = $link_ids["REX_INPUT_LINK"][1];
	$settings['linkname'] = trim($link_names["REX_LINK_NAME"][1]);
	$settings['link_abmeldung'] = $link_ids["REX_INPUT_LINK"][2];
	$settings['linkname_abmeldung'] = trim($link_names["REX_LINK_NAME"][2]);
	$settings['default_test_article'] = $link_ids["REX_INPUT_LINK"][3];
	$settings['default_test_article_name'] = trim($link_names["REX_LINK_NAME"][3]);

	$settings['autocleanup'] = array_key_exists('autocleanup', $settings) ? "active" : "inactive";
	$settings['autosend'] = array_key_exists('autosend', $settings) ? "active" : "inactive";

	// import yform-manager tablesets
	if ($settings['use_yform'] && rex_plugin::get('yform', 'manager')->isAvailable()) {
	    if (!rex_yform_manager_table::get(rex::getTablePrefix() .'375_user')) {
            $content = file_get_contents($this->getPath('snippets') . "/yform_manager_tableset_user.json");
            \rex_yform_manager_table_api::importTablesets($content);
        }
    }

	// Save settings
	if(rex_config::set("multinewsletter", $settings)) {
		echo rex_view::success(rex_i18n::msg('multinewsletter_changes_saved'));
		
		// Install / remove Cronjobs
		$cronjob_cleanup = multinewsletter_cronjob_cleanup::factory();
		if($this->getConfig('autocleanup') == 'active') {
			if(!$cronjob_cleanup->isInstalled()) {
				$cronjob_cleanup->install();
			}
		}
		else {
			$cronjob_cleanup->delete();
		}
		$cronjob_sender = multinewsletter_cronjob_sender::factory();
		if($this->getConfig('autosend') == 'active') {
			if(!$cronjob_sender->isInstalled()) {
				$cronjob_sender->install();
			}
		}
		else {
			$cronjob_sender->delete();
		}
	}
	else {
		echo rex_view::error(rex_i18n::msg('multinewsletter_changes_not_saved'));
	}
}

// Needed more than once
$langs = [];
foreach(rex_clang::getAll() as $rex_clang) {
	$langs[$rex_clang->getId()] = $rex_clang->getName();
}

?>
<form action="<?php print rex_url::currentBackendPage(); ?>" method="post">
	<div class="panel panel-edit">
		<header class="panel-heading"><div class="panel-title"><?php print rex_i18n::msg('multinewsletter_menu_config'); ?></div></header>
		<div class="panel-body">
			<fieldset>
				<legend><?php echo rex_i18n::msg('multinewsletter_config_title_standards'); ?></legend>
				<div class="panel-body-wrapper slide">
					<?php
                        d2u_addon_backend_helper::form_select('multinewsletter_config_method', 'settings[method]', ['redaxo' => rex_i18n::msg('multinewsletter_config_method_redaxo'), 'socket' => rex_i18n::msg('multinewsletter_config_method_socket')], [$this->getConfig('method', 'redaxo')]);
                        d2u_addon_backend_helper::form_select('multinewsletter_config_use_yform', 'settings[use_yform]', [0 => rex_i18n::msg('no'), 1 => rex_i18n::msg('yes')], [$this->getConfig('use_yform', 0)]);
                        d2u_addon_backend_helper::form_select('multinewsletter_config_allow_recipient_selection', 'settings[allow_recipient_selection]', [0 => rex_i18n::msg('no'), 1 => rex_i18n::msg('yes')], [$this->getConfig('allow_recipient_selection', 0)]);
						d2u_addon_backend_helper::form_input('multinewsletter_config_sender', "settings[sender]", $this->getConfig('sender'), TRUE, FALSE, "email");
                        d2u_addon_backend_helper::form_select('multinewsletter_config_defaultlang', 'settings[lang_fallback]', [0 => rex_i18n::msg('multinewsletter_lang_no_fallback'), 1 => rex_i18n::msg('multinewsletter_lang_d2u_helper')], array($this->getConfig('lang_fallback')));
						d2u_addon_backend_helper::form_linkfield('multinewsletter_config_link', 1, $this->getConfig('link'), rex_config::get("d2u_helper", "default_lang", rex_clang::getStartId()));
						d2u_addon_backend_helper::form_linkfield('multinewsletter_config_link_abmeldung', 2, $this->getConfig('link_abmeldung'), rex_config::get("d2u_helper", "default_lang", rex_clang::getStartId()));

						d2u_addon_backend_helper::form_input('multinewsletter_config_admin_email', 'settings[admin_email]', $this->getConfig('admin_email'), TRUE, FALSE, 'email');
						d2u_addon_backend_helper::form_input('multinewsletter_config_subscribe_meldung_email', 'settings[subscribe_meldung_email]', $this->getConfig('subscribe_meldung_email'), FALSE, FALSE, 'email');
						if(rex_addon::get('cronjob')->isAvailable()) {
							d2u_addon_backend_helper::form_checkbox('multinewsletter_config_autosend', 'settings[autosend]', 'active', $this->getConfig('autosend') == 'active' && multinewsletter_cronjob_sender::factory()->isInstalled());
							d2u_addon_backend_helper::form_checkbox('multinewsletter_config_autocleanup', 'settings[autocleanup]', 'active', $this->getConfig('autocleanup') == 'active' && multinewsletter_cronjob_cleanup::factory()->isInstalled());
						}
						else {
							d2u_addon_backend_helper::form_infotext('multinewsletter_config_install_cronjob', 'autosend_info');
						}
					?>
					<br/>
					<h4 style="border-bottom:1px solid #ccc;">Versandoptionen</h4>
					<?php
						d2u_addon_backend_helper::form_select('multinewsletter_config_use_smtp', 'settings[use_smtp]', [0 => rex_i18n::msg('multinewsletter_config_use_smtp_phpmailer'), 1 => rex_i18n::msg('yes')], [$this->getConfig('use_smtp', 0)]);
						d2u_addon_backend_helper::form_input('phpmailer_bcc', 'settings[smtp_bcc]', $this->getConfig('smtp_bcc'));
						d2u_addon_backend_helper::form_input('phpmailer_host', 'settings[smtp_host]', $this->getConfig('smtp_host'));
						d2u_addon_backend_helper::form_input('phpmailer_port', 'settings[smtp_port]', $this->getConfig('smtp_port'), FALSE, FALSE, 'number');
						d2u_addon_backend_helper::form_select('phpmailer_smtp_secure', 'settings[smtp_crypt]', ['' => rex_i18n::msg('no'), 'ssl' => 'ssl', 'tls' => 'tls'], [$this->getConfig('smtp_crypt', [])]);
						d2u_addon_backend_helper::form_select('phpmailer_smtp_auth', 'settings[smtp_auth]', [0 => rex_i18n::msg('no'), 1 => rex_i18n::msg('yes')], [$this->getConfig('smtp_auth', [])]);
						d2u_addon_backend_helper::form_input('phpmailer_smtp_username', 'settings[smtp_user]', $this->getConfig('smtp_user'));
						d2u_addon_backend_helper::form_input('phpmailer_smtp_password', 'settings[smtp_password]', $this->getConfig('smtp_password'));
					?>
					<script>
						function changeType() {
							if($('select[name="settings\\[use_smtp\\]"]').val() === "0") {
								$('#settings\\[smtp_bcc\\]').hide();
								$('#settings\\[smtp_host\\]').hide();
								$('#settings\\[smtp_port\\]').hide();
								$('#settings\\[smtp_crypt\\]').hide();
								$('#settings\\[smtp_auth\\]').hide();
								$('#settings\\[smtp_user\\]').hide();
								$('#settings\\[smtp_password\\]').hide();
							}
							else {
								$('#settings\\[smtp_bcc\\]').show();
								$('#settings\\[smtp_host\\]').show();
								$('#settings\\[smtp_port\\]').show();
								$('#settings\\[smtp_crypt\\]').show();
								$('#settings\\[smtp_auth\\]').show();
								$('#settings\\[smtp_user\\]').show();
								$('#settings\\[smtp_password\\]').show();
							}
						}

						// On init
						changeType();
						// On change
						$('select[name="settings\\[use_smtp\\]"]').on('change', function() {
							changeType();
						});
					</script>
					<br/>
				</div>
			</fieldset>

			<fieldset>
				<legend><?php echo rex_i18n::msg('multinewsletter_config_title_serverlimits'); ?></legend>
				<div class="panel-body-wrapper slide">
					<dl class="rex-form-group form-group">
						<dt><label for="expl_config_standards"></label></dt>
						<dd><?php print rex_i18n::msg('multinewsletter_expl_config_standards'); ?></dd>
					</dl>
					<?php
						d2u_addon_backend_helper::form_input('multinewsletter_config_max_mails', 'settings[max_mails]', $this->getConfig('max_mails'), FALSE, FALSE, 'number');
						d2u_addon_backend_helper::form_input('multinewsletter_config_versandschritte_nacheinander', 'settings[versandschritte_nacheinander]', $this->getConfig('versandschritte_nacheinander'), FALSE, FALSE, 'number');
						d2u_addon_backend_helper::form_input('multinewsletter_config_sekunden_pause', 'settings[sekunden_pause]', $this->getConfig('sekunden_pause'), FALSE, FALSE, 'number');
					?>
				</div>
			</fieldset>

			<fieldset>
				<legend><?php echo rex_i18n::msg('multinewsletter_config_title_mailchimp'); ?></legend>
				<div class="panel-body-wrapper slide">
					<dl class="rex-form-group form-group">
						<dt><label for="expl_config_standards"></label></dt>
						<dd><?php print rex_i18n::msg('multinewsletter_expl_config_mailchimp'); ?></dd>
					</dl>
					<?php
						d2u_addon_backend_helper::form_input('multinewsletter_config_mailchimp_api_key', 'settings[mailchimp_api_key]', $this->getConfig('mailchimp_api_key'));
					?>
				</div>
			</fieldset>

			<fieldset>
				<legend><?php echo rex_i18n::msg('multinewsletter_config_title_testmails'); ?></legend>
				<div class="panel-body-wrapper slide">
					<?php
						d2u_addon_backend_helper::form_linkfield('multinewsletter_config_default_test_article', 3, $this->getConfig('default_test_article'), MultinewsletterNewsletter::getFallbackLang(rex_clang::getStartId()));

						$options_anrede = [];
						$options_anrede[0] = rex_i18n::msg('multinewsletter_config_lang_title_male');
						$options_anrede[1] = rex_i18n::msg('multinewsletter_config_lang_title_female');
						d2u_addon_backend_helper::form_select('multinewsletter_config_default_test_anrede', 'settings[default_test_anrede]', $options_anrede, [$this->getConfig('default_test_anrede')]);

						d2u_addon_backend_helper::form_input('multinewsletter_config_default_test_vorname', "settings[default_test_vorname]", $this->getConfig('default_test_vorname'));
						d2u_addon_backend_helper::form_input('multinewsletter_config_default_test_nachname', "settings[default_test_nachname]", $this->getConfig('default_test_nachname'));
						d2u_addon_backend_helper::form_input('multinewsletter_config_default_test_email', "settings[default_test_email]", $this->getConfig('default_test_email'), FALSE, FALSE, 'email');

						d2u_addon_backend_helper::form_select('multinewsletter_config_default_test_sprache', 'settings[default_test_sprache]', $langs, [$this->getConfig('default_test_sprache')]);
					?>
				</div>
			</fieldset>

			<?php
				foreach(rex_clang::getAll() as $rex_clang) {
			?>
			<fieldset>
				<legend><?php echo rex_i18n::msg('multinewsletter_config_langname_section') ." ". $rex_clang->getName(); ?></legend>
				<div class="panel-body-wrapper slide">
					<?php
						print '<button class="btn btn-save rex-form-aligned preset-button" name="preset" id="preset_button_'. $rex_clang->getId() .'" onclick="setVisibility('. $rex_clang->getId() .')" type="button">';
						echo rex_i18n::msg('multinewsletter_config_lang_presets');
						print ' </button>';

						print '<ul id="dropdown-'. $rex_clang->getId() .'" class="preset-dropdown" data-clang="'. $rex_clang->getId() .'">';
						foreach ($lang_presets as $settings_key => $value) {
							echo '<li data-langpreset-id="' . $settings_key . '"><a href="#' . $settings_key . '" onclick="setVisibility('. $rex_clang->getId() .')">' . $value['language'] . '</a></li>';
						}
						print '</ul>';

						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_sendername', "settings[lang_". $rex_clang->getId() ."_sendername]", $this->getConfig('lang_'. $rex_clang->getId() .'_sendername', ''));
					?>

					<dl class="rex-form-group form-group">
						<dt><label for="lang_anmeldeformular"></label></dt>
						<dd><b><?php print rex_i18n::msg('multinewsletter_config_lang_anmeldeformular'); ?></b></dd>
					</dl>
					<hr style="border-top: 1px solid #333">
					<?php
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_anrede', 'settings[lang_'. $rex_clang->getId() .'_anrede]', $this->getConfig('lang_'. $rex_clang->getId() .'_anrede', ''));
						d2u_addon_backend_helper::form_select('multinewsletter_config_title', 'settings[lang_'. $rex_clang->getId() .'_title]', $options_anrede, [$this->getConfig('lang_'. $rex_clang->getId() .'_title')]);
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_title_male', "settings[lang_". $rex_clang->getId() ."_title_0]", $this->getConfig('lang_'. $rex_clang->getId() .'_title_0', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_title_female', "settings[lang_". $rex_clang->getId() ."_title_1]", $this->getConfig('lang_'. $rex_clang->getId() .'_title_1', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_grad', "settings[lang_". $rex_clang->getId() ."_grad]", $this->getConfig('lang_'. $rex_clang->getId() .'_grad', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_firstname', "settings[lang_". $rex_clang->getId() ."_firstname]", $this->getConfig('lang_'. $rex_clang->getId() .'_firstname', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_lastname', "settings[lang_". $rex_clang->getId() ."_lastname]", $this->getConfig('lang_'. $rex_clang->getId() .'_lastname', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_email', "settings[lang_". $rex_clang->getId() ."_email]", $this->getConfig('lang_'. $rex_clang->getId() .'_email', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_select_newsletter', "settings[lang_". $rex_clang->getId() ."_select_newsletter]", $this->getConfig('lang_'. $rex_clang->getId() .'_select_newsletter', ''));
						d2u_addon_backend_helper::form_textarea('multinewsletter_config_lang_privacy_policy', "settings[lang_". $rex_clang->getId() ."_privacy_policy]", stripslashes($this->getConfig('lang_'. $rex_clang->getId() .'_privacy_policy', '')), 3, FALSE, FALSE, FALSE);
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_compulsory', "settings[lang_". $rex_clang->getId() ."_compulsory]", $this->getConfig('lang_'. $rex_clang->getId() .'_compulsory', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_subscribe', "settings[lang_". $rex_clang->getId() ."_subscribe]", $this->getConfig('lang_'. $rex_clang->getId() .'_subscribe', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_action', "settings[lang_". $rex_clang->getId() ."_action]", $this->getConfig('lang_'. $rex_clang->getId() .'_action', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_safety', "settings[lang_". $rex_clang->getId() ."_safety]", $this->getConfig('lang_'. $rex_clang->getId() .'_safety', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_status1', "settings[lang_". $rex_clang->getId() ."_status1]", $this->getConfig('lang_'. $rex_clang->getId() .'_status1', ''));
					?>
					<hr style="border-top: 1px solid #333">
					<dl class="rex-form-group form-group">
						<dt><label for="lang_anmeldeformular_fehler"></label></dt>
						<dd><b><?php print rex_i18n::msg('multinewsletter_config_lang_anmeldeformular_fehler'); ?></b></dd>
					</dl>
					<?php
						d2u_addon_backend_helper::form_textarea('multinewsletter_config_lang_no_userdata', "settings[lang_". $rex_clang->getId() ."_no_userdata]", stripslashes($this->getConfig('lang_'. $rex_clang->getId() .'_no_userdata', '')), 3, FALSE, FALSE, FALSE);
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_invalid_email', "settings[lang_". $rex_clang->getId() ."_invalid_email]", $this->getConfig('lang_'. $rex_clang->getId() .'_invalid_email', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_invalid_firstname', "settings[lang_". $rex_clang->getId() ."_invalid_firstname]", $this->getConfig('lang_'. $rex_clang->getId() .'_invalid_firstname', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_invalid_lastname', "settings[lang_". $rex_clang->getId() ."_invalid_lastname]", $this->getConfig('lang_'. $rex_clang->getId() .'_invalid_lastname', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_send_error', "settings[lang_". $rex_clang->getId() ."_send_error]", $this->getConfig('lang_'. $rex_clang->getId() .'_send_error', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_software_failure', "settings[lang_". $rex_clang->getId() ."_software_failure]", $this->getConfig('lang_'. $rex_clang->getId() .'_software_failure', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_nogroup_selected', "settings[lang_". $rex_clang->getId() ."_nogroup_selected]", $this->getConfig('lang_'. $rex_clang->getId() .'_nogroup_selected', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_already_subscribed', "settings[lang_". $rex_clang->getId() ."_already_subscribed]", $this->getConfig('lang_'. $rex_clang->getId() .'_already_subscribed', ''));
						d2u_addon_backend_helper::form_textarea('multinewsletter_config_lang_confirmation_sent', "settings[lang_". $rex_clang->getId() ."_confirmation_sent]", stripslashes($this->getConfig('lang_'. $rex_clang->getId() .'_confirmation_sent', '')), 3, FALSE, FALSE, FALSE);
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_confirmsubject', "settings[lang_". $rex_clang->getId() ."_confirmsubject]", $this->getConfig('lang_'. $rex_clang->getId() .'_confirmsubject', ''));
						d2u_addon_backend_helper::form_textarea('multinewsletter_config_lang_confirmcontent', "settings[lang_". $rex_clang->getId() ."_confirmcontent]", stripslashes($this->getConfig('lang_'. $rex_clang->getId() .'_confirmcontent', '')), 15, FALSE, FALSE, FALSE);
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_already_confirmed', "settings[lang_". $rex_clang->getId() ."_already_confirmed]", $this->getConfig('lang_'. $rex_clang->getId() .'_already_confirmed', ''));
						d2u_addon_backend_helper::form_textarea('multinewsletter_config_lang_invalid_key', "settings[lang_". $rex_clang->getId() ."_invalid_key]", stripslashes($this->getConfig('lang_'. $rex_clang->getId() .'_invalid_key', '')), 2, FALSE, FALSE, FALSE);
						d2u_addon_backend_helper::form_textarea('multinewsletter_config_lang_confirmation_successful', "settings[lang_". $rex_clang->getId() ."_confirmation_successful]", stripslashes($this->getConfig('lang_'. $rex_clang->getId() .'_confirmation_successful', '')), 5, FALSE, FALSE, FALSE);
					?>
					<hr style="border-top: 1px solid #333">
					<dl class="rex-form-group form-group">
						<dt><label for="lang_abmeldeformular"></label></dt>
						<dd><b><?php print rex_i18n::msg('multinewsletter_config_lang_abmeldeformular'); ?></b></dd>
					</dl>
					<?php
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_unsubscribe', "settings[lang_". $rex_clang->getId() ."_unsubscribe]", $this->getConfig('lang_'. $rex_clang->getId() .'_unsubscribe', ''));
						d2u_addon_backend_helper::form_textarea('multinewsletter_config_lang_status0', "settings[lang_". $rex_clang->getId() ."_status0]", stripslashes($this->getConfig('lang_'. $rex_clang->getId() .'_status0', '')), 2, FALSE, FALSE, FALSE);
					?>

					<hr style="border-top: 1px solid #333">
					<dl class="rex-form-group form-group">
						<dt><label for="lang_abmeldeformular_fehler"></label></dt>
						<dd><b><?php print rex_i18n::msg('multinewsletter_config_lang_abmeldeformular_fehler'); ?></b></dd>
					</dl>
					<?php
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_already_unsubscribed', "settings[lang_". $rex_clang->getId() ."_already_unsubscribed]", $this->getConfig('lang_'. $rex_clang->getId() .'_already_unsubscribed', ''));
						d2u_addon_backend_helper::form_input('multinewsletter_config_lang_user_not_found', "settings[lang_". $rex_clang->getId() ."_user_not_found]", $this->getConfig('lang_'. $rex_clang->getId() .'_user_not_found', ''));
					?>
				</div>
			</fieldset>
			<?php
				}
			?>
		</div>
		<footer class="panel-footer">
			<div class="rex-form-panel-footer">
				<div class="btn-toolbar">
					<button class="btn btn-save rex-form-aligned" type="submit" name="btn_save" value="Speichern"><?php echo rex_i18n::msg('multinewsletter_config_submit'); ?></button>
				</div>
			</div>
		</footer>
	</div>
</form>

<style>
	/* Slide fieldsets*/
	div.panel-body legend {
		background: transparent url("<?php echo $this->getAssetsUrl('arrows.png'); ?>") no-repeat 0px 7px;
		padding-left: 19px;
	}
	div.panel-body legend.open {
		background-position: 0px -36px;
	}

	.panel-body-wrapper.slide {
		display: none;
	}

	/* Preset Buttons */
	.preset-button {
		float: right;
		margin-top: -60px;
	}
	.preset-button:after {
		content: "↓";
	}
	.preset-dropdown {
		position: absolute;
		z-index: 100;
		display: none;
		min-width: 160px;
		padding: 5px 20px;
		list-style: none;
		font-size: 14px;
		line-height: 2.5em;
		background-color: #fff;
		border: 1px solid #ccc;
		-webkit-box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
		margin-top: -25px;
		right: 30px;
	}
	.preset-dropdown a {
		color: #324050;
	}
	.preset-dropdown a:hover {
		text-decoration: none;
	}
</style>

<script>
	function setVisibility(id) {
		if(document.getElementById('dropdown-' + id).style.display === 'inherit'){
			document.getElementById('dropdown-' + id).style.display = 'none';
		} else {
			document.getElementById('dropdown-' + id).style.display = 'inherit';
		}
	}

	jQuery(document).ready(function($) {
		// presets
		var langPresets = <?php echo json_encode($lang_presets); ?>;

		$('ul.preset-dropdown li').click(function(e) {
			var curClang = $(this).parent().attr('data-clang');
			var curLangPresetIndex = $(this).attr('data-langpreset-id');

			$('[name="settings[lang_' + curClang + '_anrede]"]').val(langPresets[curLangPresetIndex]['anrede']);
			$('[name="settings[lang_' + curClang + '_title_0]"]').val(langPresets[curLangPresetIndex]['title_0']);
			$('[name="settings[lang_' + curClang + '_title_1]"]').val(langPresets[curLangPresetIndex]['title_1']);
			$('[name="settings[lang_' + curClang + '_confirmsubject]"]').val(langPresets[curLangPresetIndex]['confirmsubject']);
			$('[name="settings[lang_' + curClang + '_confirmcontent]"').val(langPresets[curLangPresetIndex]['confirmcontent']);
//			$('[name="settings[lang_' + curClang + '_sendername]"]').val(langPresets[curLangPresetIndex]['sendername']);
			$('[name="settings[lang_' + curClang + '_privacy_policy]"]').val(langPresets[curLangPresetIndex]['privacy_policy']);
			$('[name="settings[lang_' + curClang + '_compulsory]"]').val(langPresets[curLangPresetIndex]['compulsory']);
			$('[name="settings[lang_' + curClang + '_action]"]').val(langPresets[curLangPresetIndex]['action']);
			$('[name="settings[lang_' + curClang + '_invalid_email]"]').val(langPresets[curLangPresetIndex]['invalid_email']);
			$('[name="settings[lang_' + curClang + '_invalid_firstname]"]').val(langPresets[curLangPresetIndex]['invalid_firstname']);
			$('[name="settings[lang_' + curClang + '_invalid_lastname]"]').val(langPresets[curLangPresetIndex]['invalid_lastname']);
			$('[name="settings[lang_' + curClang + '_send_error]"]').val(langPresets[curLangPresetIndex]['send_error']);
			$('[name="settings[lang_' + curClang + '_software_failure]"]').val(langPresets[curLangPresetIndex]['software_failure']);
			$('[name="settings[lang_' + curClang + '_no_userdata]"]').val(langPresets[curLangPresetIndex]['no_userdata']);
			$('[name="settings[lang_' + curClang + '_already_unsubscribed]"]').val(langPresets[curLangPresetIndex]['already_unsubscribed']);
			$('[name="settings[lang_' + curClang + '_already_subscribed]"]').val(langPresets[curLangPresetIndex]['already_subscribed']);
			$('[name="settings[lang_' + curClang + '_already_confirmed]"]').val(langPresets[curLangPresetIndex]['already_confirmed']);
			$('[name="settings[lang_' + curClang + '_user_not_found]"]').val(langPresets[curLangPresetIndex]['user_not_found']);
			$('[name="settings[lang_' + curClang + '_safety]"]').val(langPresets[curLangPresetIndex]['safety']);
			$('[name="settings[lang_' + curClang + '_status0]"]').val(langPresets[curLangPresetIndex]['status0']);
			$('[name="settings[lang_' + curClang + '_status1]"]').val(langPresets[curLangPresetIndex]['status1']);
			$('[name="settings[lang_' + curClang + '_invalid_key]"]').val(langPresets[curLangPresetIndex]['invalid_key']);
			$('[name="settings[lang_' + curClang + '_confirmation_successful]"]').val(langPresets[curLangPresetIndex]['confirmation_successful']);
			$('[name="settings[lang_' + curClang + '_confirmation_sent]"]').val(langPresets[curLangPresetIndex]['confirmation_sent']);
			$('[name="settings[lang_' + curClang + '_email]"]').val(langPresets[curLangPresetIndex]['email']);
			$('[name="settings[lang_' + curClang + '_firstname]"]').val(langPresets[curLangPresetIndex]['firstname']);
			$('[name="settings[lang_' + curClang + '_lastname]"]').val(langPresets[curLangPresetIndex]['lastname']);
			$('[name="settings[lang_' + curClang + '_grad]"]').val(langPresets[curLangPresetIndex]['grad']);
			$('[name="settings[lang_' + curClang + '_select_newsletter]"]').val(langPresets[curLangPresetIndex]['select_newsletter']);
			$('[name="settings[lang_' + curClang + '_subscribe]"]').val(langPresets[curLangPresetIndex]['subscribe']);
			$('[name="settings[lang_' + curClang + '_unsubscribe]"]').val(langPresets[curLangPresetIndex]['unsubscribe']);
			$('[name="settings[lang_' + curClang + '_nogroup_selected]"]').val(langPresets[curLangPresetIndex]['nogroup_selected']);
    	});

		// slide fieldsets
		$('legend').click(function(e) {
			$(this).toggleClass('open');
			$(this).next('.panel-body-wrapper.slide').slideToggle();
		});
	});
	
	// Open all fieldsets when save was clicked for being able to focus required fields
	$('button[type=submit]').click(function() {
		$('legend').each(function() {
			if(!$(this).hasClass('open')) {
				$(this).addClass('open');
				$(this).next('.panel-body-wrapper.slide').slideToggle();
			}
		});
		return true;
	});
</script>