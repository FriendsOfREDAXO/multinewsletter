<fieldset>
	<legend>MultiNewsletter Updatehinweise</legend>
	<p>3.6.0</p>
	<ul>
		<li>WICHTIG: Anreden um die diverse Anrede Mx. und auch ohne Anrede erweitert.
			In den Einstellungen -> Übersetzungen bitte für diese beiden Felder ergänzen
			und speichern.</li>
		<li>WICHTIG: Vorbereitung auf R6: Folgende Klassen werden ab Version 2 dieses Addons umbenannt. Schon jetzt stehen die neuen Klassen für die Übergangszeit zur Verfügung:
			<ul>
				<li><code>MultinewsletterGroup</code> wird zu <code>FriendsOfRedaxo\MultiNewsletter\Group</code>.</li>
				<li><code>MultinewsletterMailchimp</code> wird zu <code>FriendsOfRedaxo\MultiNewsletter\Mailchimp</code>.</li>
				<li><code>MultinewsletterMailchimpException</code> wird zu <code>FriendsOfRedaxo\MultiNewsletter\MailchimpException</code>.</li>
				<li><code>MultinewsletterNewsletter</code> wird zu <code>FriendsOfRedaxo\MultiNewsletter\Newsletter</code>.</li>
				<li><code>MultinewsletterNewsletterManager</code> wird zu <code>FriendsOfRedaxo\MultiNewsletter\NewsletterManager</code>.</li>
				<li><code>MultinewsletterUser</code> wird zu <code>FriendsOfRedaxo\MultiNewsletter\User</code>.</li>
				<li><code>MultinewsletterUserList</code> wird zu <code>FriendsOfRedaxo\MultiNewsletter\Userlist</code>.</li>
			</ul>
			Folgende interne Klassen wurden wurden ebenfalls umbenannt. Hier gibt es keine Übergangszeit, da sie nicht öffentlich sind:
			<ul>
				<li><code>D2UMultiNewsletterModules</code> wird zu <code>FriendsOfRedaxo\MultiNewsletter\Module</code>.</li>
				<li><code>multinewsletter_cronjob_cleanup</code> wird zu <code>FriendsOfRedaxo\MultiNewsletter\CronjobCleanup</code>.</li>
				<li><code>multinewsletter_cronjob_sender</code> wird zu <code>FriendsOfRedaxo\MultiNewsletter\CronjobSender</code>.</li>
				
			</ul>
		</li>
	</ul>

	<p>3.2.1:</p>
	<ul>
		<li>Achtung Entwickler: Die Klasse MultiNewsletterAbstract gibt es nicht
			mehr. Die Klasse MultinewsletterUser unterstützt nun die Funktionen
			getValue(), setValue() und getId() nicht mehr.</li>
		<li>DSGVO Verbesserung: Es sollte unbedingt die AutoCleanUp Option in
			den Einstellungen aktiviert werden. Diese Option löscht Abonnenten,
			die ihre Anmeldung innerhalb von 4 Wochen nicht bestätigt haben und
			ersetzt Empfänger Adressen nach 4 Wochen in den Archiven.</li>
	</ul>
	<p>3.2.0:</p>
	<ul>
		<li>Bitte Admin E-Mail-Adresse in den Einstellungen hinterlegen.</li>
	</ul>
	<p>3.0.0:</p>
	<ul>
		<li>Vor einem Update von Redaxo 4 auf Redaxo 5 müssen die MultiNewsletter
		Datenbanktabellen in Version >= 2.2.0 vorhanden sein. Beim Installieren
		oder Reinstallieren von MultiNewsletter werden die Tabellen automatisch
		entsprechend angepasst.</li>
		<li>Die Einstellungen und Übersetzungen werden nicht übernommen und müssen
		manuell eingegeben werden.</li>
		<li>Die Module müssen manuell aktualisiert werden.</li>
	</ul>
	<p>2.2.0:</p>
	<ul>
		<li>Falls nicht über das Installer Addon aktualisiert, Addon bitte reinstallieren.</li>
	</ul>
	<p>2.1.0:</p>
	<ul>
		<li>Bitte Module aktualisieren.</li>
	</ul>
	<p>2.0.0:</p>
	<ul>
		<li>Einstellungen werden beim ersten Aufruf einer Seite des Addons soweit
			möglich übernommen. Bitte die neuen Einstellungen prüfen und speichern.</li>
		<li>WICHTIG: Module bitte von Hand aktualisieren und auch die zugehörigen Artikel!</li>
		<li>Die Datenbank wird von selbst aktualisiert.</li>
		<li>Nicht mehr benötigte Dateien werden automatisch gelöscht, sofern die
			nötigen Dateirechte vorliegen.</li>
		<li>Beim ersten Aufruf einer Seite des Addons werden nach der Datenübernahme
			die Übersetzungen nicht geladen und angezeigt.</li>
	</ul>
</fieldset>