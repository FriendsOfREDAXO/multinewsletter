<fieldset>
	<legend>Hinweise zum Versand</legend>
	<p>Folgende Platzhalter werden vor dem Versand ersetzt:</p>
	<ul>
		<li>+++AKTIVIERUNGSLINK+++ ist der personalisierte Link in der Aktivierungsmail.</li>
		<li>+++ABMELDELINK+++ ist der personalisierte Abmeldenlink.</li>
		<li>+++NEWSLETTERLINK+++ ist der Link zur Onlineversion des Newsletters.</li>
		<li>+++TITLE+++ ist die Anrede des Empfängers.</li>
		<li>+++GRAD+++ ist der akademische Grad des Empfängers.</li>
		<li>+++FIRSTNAME+++ ist der Vorname des Empfängers.</li>
		<li>+++LASTNAME+++ ist der Nachname des Empfängers.</li>
		<li>+++EMAIL+++ ist die E-Mail-Adresse des Empfängers.</li>
		<li>+++LINK_PRIVACY_POLICY+++ ist der Link zur Datenschutzerklärung. Er wird in den <a href="<?= rex_url::backendPage('d2u_helper/settings') ?>">Einstellungen des D2U Helper Addon</a> festgelegt.</li>
		<li>+++LINK_IMPRESS+++ ist der Link zum Impressum. Er wird in den <a href="<?= rex_url::backendPage('d2u_helper/settings') ?>">Einstellungen des D2U Helper Addon</a> festgelegt.</li>
	</ul>
	<p>Wo werden obige Platzhalter ersetzt?</p>
	<ul>
		<li>Betreff des Newsletters (Testmail und endgültiger Versand)</li>
		<li>Textkörper des Newsletters (Testmail und endgültiger Versand)</li>
		<li>Betreff der Aktivierungsmail</li>
		<li>Textkörper der Aktivierungsmail</li>
		<li>+++LINK_PRIVACY_POLICY+++ und +++LINK_IMPRESS+++ werden durch das D2U
			Helper Addon im gesamten Frontend ersetzt.</li>
	</ul>
	<p>Bitte beachten Sie, dass ab Version 2 des MultiNewsletters nach Schritt 3
		Änderungen am Redaxo Artikel nicht mehr beim Versand berücksichtigt werden.
		Der Artikel wird nicht mehr wie früher bei jedem Versandschritt neu ausgelesen,
		sondern nur einmalig beim Vorbereiten.</p>
</fieldset>