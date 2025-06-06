<fieldset>
	<legend>MultiNewsletter Changelog</legend>
	<p>3.6.7-DEV</p>
	<ul>
		<li>Modul 80-4 "MultiNewsletter YForm Anmeldung": Ergebnismeldung Bootstrap konform formatiert.</li>
	</ul>
	<p>3.6.6</p>
	<ul>
		<li>Bugfix: Fehler im Modul 80-2 "MultiNewsletter Abmeldung" behoben.</li>
		<li>Bugfix: Fehler im Modul 80-3 "MultiNewsletter Anmeldung nur mit Mail" behoben.</li>
		<li>Bugfix: Fehler im Modul 80-4 "MultiNewsletter YForm Anmeldung" behoben und Platzhalter entfernt.</li>
	</ul>
	<p>3.6.5</p>
	<ul>
		<li>Bugfix: Import gleicher E-Mail-Adressen mit unterschiedlicher Groß- und Kleinschreibung unterbunden.</li>
		<li>Bugfix Modul 80-1 "MultiNewsletter Anmeldung mit Name und Anrede": nach der rexstan Überarbeiten hatten sich Fehler bei den Gruppen eingeschlichen.</li>
		<li>Dokumentation zu Pflichtfelder beim Import verbessert.</li>
	</ul>
	<p>3.6.4</p>
	<ul>
		<li>Bugfix: Voreinstellungen für Testmails zeigte die gespeicherte Anrede nicht korrekt an.</li>
	</ul>
	<p>3.6.3</p>
	<ul>
		<li>Bugfix: Ersetzungen der Benutzerdaten im Frontent schlug fehl. Platzhalter für Newsletterlink wurde automatisch angepasst.</li>
		<li>Bugfix: Beispieltemplate in der Hilfe wurde nicht angezeigt.</li>
	</ul>
	<p>3.6.2</p>
	<ul>
		<li>Bugfix: Filter im Backend der Benutzerliste funktioniert nun auch Auswahl von Guppen und Status @jfax.</li>
	</ul>
	<p>3.6.1</p>
	<ul>
		<li>Bugfix bei der manuellen Artikelauswahl im Newsletterversand.</li>
	</ul>
	<p>3.6.0</p>
	<ul>
		<li>Transfer des Repos zu FriendsOfRedaxo.</li>
		<li>Vorbereitung auf R6: Folgende Klassen werden ab Version 2 dieses Addons umbenannt. Schon jetzt stehen die neuen Klassen für die Übergangszeit zur Verfügung:
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
		<li>Addon komplett mit rexstan Level 9 überarbeitet.</li>
		<li>WICHTIG: Anreden um die diverse Anrede Mx. und auch ohne Anrede erweitert. In den Einstellungen -> Übersetzungen bitte für diese beiden Felder ergänzen und speichern.</li>
		<li>E-Mails nach Cronjob Versand enthalten nun auch Vor- und Nachnamen der Empfänger, sofern diese vorhanden sind.</li>
		<li>Modul 80-4 "MultiNewsletter YForm Anmeldung": unterstützt jetzt auch das Addon YForm Spamprotection.</li>
	</ul>
	<p>3.5.5</p>
	<ul>
		<li>Bugfix beim CSV Import: IP Adressfilter gab manchmal null zurück, erwartet wurde aber ein leerer String.</li>
		<li>Bugfix: wenn in den Einstellungen festgelegt war, dass der Artikel per Socket ausgelesen werden soll, wurden personenbezogene Ersetzungen nicht ersetzt.</li>
		<li>Modul 80-3 "MultiNewsletter Anmeldung nur mit Mail": Fehler beim Zuweisen der Anrede des Users behoben.</li>
	</ul>
	<p>3.5.4</p>
	<ul>
		<li>Modul 80-4 "MultiNewsletter YForm Anmeldung": Fehler im Spamschutz / CSRF Schutz behoben.</li>
		<li>Modul 80-5 "MultiNewsletter YForm Abmeldung": Fehler im Spamschutz / CSRF Schutz behoben.</li>
	</ul>
	<p>3.5.3</p>
	<ul>
		<li>Bugfix: Einige Einstellungen wurden nicht korrekt angezeigt, obwohl sie gespeichert waren.</li>
		<li>Bugfix: Bei aktivierter Option "Usertabelle im YForm Table-Manager verfügbar machen" in den Einstellungen wird nun das Benutzer Tab trotzdem angezeigt, obwohl die Daten auch über den YForm Tablemanager bearbeitet werden können.</li>
	</ul>
	<p>3.5.2</p>
	<ul>
		<li>Bugfix: Import Seite wird wieder angezeigt.</li>
		<li>Bugfix: Wenn die Antwort An Adresse nicht gesetzt ist, kam es unter bestimmten Umständen beim versenden der Testnachricht zu einem Fatal Error.</li>
	</ul>
	<p>3.5.1</p>
	<ul>
		<li>Installer Action angepasst.</li>
	</ul>
	<p>3.5.0</p>
	<ul>
		<li>Import- und Exportfunktion der Einstellungen (@dpf-dd)</li>
		<li>Wieder ein paar rexstan Verbesserungen.</li>
	</ul>
	<p>3.4.1</p>
	<ul>
		<li>Modul 80-4 "MultiNewsletter YForm Anmeldung": Formular mit Formularnamen versehen um bessere YForm Spamprotection Kompatibilität bei mehreren Formularen auf einer Seite herzustellen.</li>
		<li>Modul 80-5 "MultiNewsletter YForm Abmeldung": Einstellung der Breite des Blocks funktioniert jetzt korrekt. Außerdem FOrmular mit Formularnamen versehen um bessere YForm Spamprotection Kompatibilität bei mehreren Formularen auf einer Seite herzustellen.</li>
	</ul>
	<p>3.4.0</p>
	<ul>
		<li>PHP-CS-Fixer Code Verbesserungen.</li>
		<li>Testmails können nun auch mit Anhängen versehen werden.</li>
		<li>Bugfix: Dateinamen der Anhänge sind nun gleich wie im Medienpool.</li>
		<li>Bugfix: Presets einer Sprache in den Einstellungen funktionieren jetzt wieder.</li>
	</ul>
	<p>3.3.1</p>
	<ul>
		<li>Anpassungen der Archivseite an den Dark Mode.</li>
		<li>Absender zur Übersicht der Archivseite hinzugefügt.</li>
		<li>Sprachcode statt Sprach-ID auf der Übersichtsseite der Archive angezeigt.</li>
		<li>Modul 80-4 "MultiNewsletter YForm Anmeldung": Datenschutzhinweis wurde doppelt angezeigt.</li>
	</ul>
	<p>3.3.0</p>
	<ul>
		<li>.github Verzeichnis aus Installer Action ausgeschlossen.</li>
		<li>Bugfix: wenn ein Empfänger während dem Versandvergang amgemeldet hat, konnte der Newsletter nie zuende verschickt werden.</li>
		<li>Bugfix: Es war nicht möglich mehrere Newsletter parallel per Cronjob zu versenden, da der vorherige Newsletter gelöscht wurde.</li>
		<li>Installationsdateien auf Redaxo Standard umgestellt</li>
		<li>Import Tabellen aus Redaxo 4 entfernt.</li>
		<li>Autosend Archiv Übersicht mit Möglichkeit zum Abbrechen auf der Newsletterseite programmiert.</li>
	</ul>
	<p>3.2.9</p>
	<ul>
		<li>Anpassungen an Publish Github Release to Redaxo.</li>
		<li>Notice beim Versand entfernt, wenn versehentlich keine Gruppe ausgewählt wurde.</li>
		<li>Beschreibung der YForm Option in den Einstellungen verbessert.</li>
		<li>Überschrift Archiv korrigert.</li>
		<li>2 PHP Notices auf der Archivseite entfernt.</li>
		<li>Modul 80-4 "MultiNewsletter YForm Anmeldung": Notice entfernt.</li>
		<li>Modul 80-4 "MultiNewsletter YForm Anmeldung": Erstellungsdatum per CSS ausgeblendet (<a href="https://github.com/yakamara/redaxo_yform/issues/1158" target="_blank">YForm Issue</a>).</li>
	</ul>
	<p>3.2.8</p>
	<ul>
		<li>Notice bezüglich Anhänge entfernt.</li>
		<li>Whoops beim Versand wenn ein Absendername ein einfaches Anführungszeichen hat behoben.</li>
		<li>Whoops beim Zurücksetzen des Versands unter MySQL 8.</li>
		<li>Bugfix: Versandeinstellungen für eigenen SMTP Server wurden bei Aktivierungsmail ignoriert.</li>
		<li>Der Versand für fehlgeschlagene Empfänger kann aus dem Archiv heraus wiederholt werden.</li>
		<li>Warnmeldung eingebaut, wenn wichtige Eintellungen noch nicht festegelegt sind, die für die Verwendung des Addons Voraussetzung sind.</li>
		<li>Modul 80-1, 80-2 und 80-3 mit Eingabefelder im Redaxo Stil.</li>
		<li>Modul 80-1 "MultiNewsletter Anmeldung mit Name und Anrede": Doppleter Versand bei von Anmeldemails bei Seitenreload behoben.</li>
		<li>Modul 80-4 "MultiNewsletter YForm Anmeldung": Aktivierungsschlüssel war nicht mit anderen Modulen kompatibel.</li>
	</ul>
	<p>3.2.7</p>
	<ul>
		<li>Möglichkeit in einer Gruppe eine Antwort An Adresse hinzuzufügen.</li>
		<li>Benötigt Redaxo >= 5.10, da die neue Klasse rex_version verwendet wird.</li>
		<li>Bugfix: Behebt Fehler wenn YRewrite verwendet wird und kein Startartikel in Redaxo angegeben ist.</li>
		<li>Bugfix: Namen können nun auch ein einzelnes Anführungszeichen haben.</li>
		<li>Backend: Einstellungen enthalten jetzt eine Option die es ermöglicht einzustellen wie der Artikel ausgelesen werden soll, ob Redaxo intern (ohne Output Filter Addons) oder per Socket (mit allen Addons). Dabei ist Redaxo Intern aus Gründen der Rückwärtskompatibilität Standard.</li>
		<li>Backend: Einstellungen, Setup und Hilfe Tabs rechts eingeordnet um sie vom Inhalt besser zu unterscheiden.</li>
		<li>Frontend: Output Filter ist nur aktiviert wenn Parameter replace_vars=1 in der URL enthalten ist.</li>
	</ul>
	<p>3.2.6</p>
	<ul>
		<li>Bugfix: CronJob Log Erfolgsmeldung wurde mehrfach ins Log geschrieben.</li>
		<li>Bugfix: YForm Module führten zu fatal error wenn Konfiguration noch nicht gespeichert war.</li>
		<li>Bugfix: Artikelname wurde im Backend beim manuellen Versand unter bestimmten Umständen falsch angezeigt, wenn der Versand unterbrochen wurde und man sich aus Redaxo ausgeloggt hatte.</li>
		<li>Bugfix: Ersetzungsvariablen +++LINK_PRIVACY_POLICY+++ und +++LINK_IMPRESS+++ wurden immer nur mit dem Link der Standardsprache ersetzt.</li>
		<li>Verhindert das Löschen von Artikeln wenn sie noch in den MultiNewsletter Einstellungen oder den Gruppen Einstellungen verwendet werden.</li>
		<li>Artikel wird nun per HTTP Socket gelesen um Addons wie Blöcks, XOutputFilter und SProg nutzbar zu machen. Sollte ein Socket Aufbau fehlschlagen wird der Inhalt des Artikels wie bisher ausgelesen.</li>
	</ul>
	<p>3.2.5</p>
	<ul>
		<li>Bugfix: wenn Titel des Newsletters ein ' enthielt gab es einen fatal error.</li>
		<li>Bugfix: Installation schlug wegen utf8mb4 Konvertierung fehl.</li>
	</ul>
	<p>3.2.4</p>
	<ul>
		<li>Bugfix Versand: Fatal Error behoben wenn keine Empfänger ausgewählt waren.</li>
		<li>Bugfix Import: war ein Leerzeichen beim Import vor oder nach einer E-Mail-Adresse eingefügt, wurde bei einem doppelten Eintrag ein fatal error angezeigt.</li>
		<li>Verbesserte Übersetzungen ausstehender Newsletter.</li>
		<li>YRewrite Multidomain support.</li>
		<li>Datenbanktabellen zu utf8mb4 konvertiert.</li>
	</ul>
	<p>3.2.3</p>
	<ul>
		<li>YForm Beispielmodule hinzugefügt.</li>
		<li>Bei Deinstallation wurde eine Tabelle nicht gelöscht.</li>
		<li>Bugfix Standardtexte.</li>
		<li>Module für Anmeldung 80-1 und 80-3: PHP 7.2 Warning entfernt und Bugfix Speichern Anmeldedatum.</li>
	</ul>
	<p>3.2.2</p>
	<ul>
		<li>Bugfix Issue #29.</li>
		<li>Bugfix: Deaktiviertes Addon zu deinstallieren führte zu fatal error (#30).</li>
		<li>Bugfix: Leerer Titel führt zu Fehler beim Speichern (#31).</li>
		<li>Bugfix: CronJob wird - wenn installiert - nicht immer richtig aktiviert.</li>
	</ul>
	<p>3.2.1</p>
	<ul>
		<li>Bugfix Issue #28.</li>
		<li>Achtung Entwickler: Die Klasse MultiNewsletterAbstract gibt es nicht mehr. Die Klasse MultinewsletterUser unterstützt nun die Funktionen getValue(), setValue() und getId() nicht mehr.</li>
		<li>Kleine Verbesserungen am Template: Einstellungen aus dem D2U Helper Addon werden übernommen.</li>
		<li>DSGVO Anpassung: CronJob zum automatischen Entfernen der Adressen von Empfängern in mehr als 4 Wochen alten Newslettern hinzugefügt. Außerdem werden Abonnenten gelöscht, die ihre Anmeldung innerhalb von 4 Wochen nicht aktiviert haben.</li>
		<li>Klasse MultiNewsletterGroupList in Klasse MultiNewsletterGroup integriert.</li>
		<li>Versandoptionen in den Einstellungen werden nur noch eingeblendet wenn ja ausgewählt ist.</li>
		<li>Benachrichtigung bei Autoversand verbessert.</li>
	</ul>
	<p>3.2.0</p>
	<ul>
		<li>Modul 80-2 Abmeldung Parameter zur Abmeldung umbenannt, damit auf einer Seite das An- und Abmeldemodul verwendet werden kann.</li>
		<li>Newsletter kann über Backend per CronJob versendet werden.</li>
		<li>Methode zum automatischen Versand per API steht zur Verfügung: <pre>MultinewsletterNewsletterManager::autosend()</pre></li>
		<li>Administrator E-Mail-Adresse in den Einstellungen hinzugefügt.</li>
		<li>Ausstehende Empfänger werden nun in eigener Tabelle gespeichert.</li>
		<li>Bugfix: Methode getName() in NewsletterGroup lieferte leeren Wert.</li>
		<li>DSGVO Hinweis im Backend wenn MailChimp genutzt wird.</li>
		<li>Activationkey von int auf varchar(45) geändert.</li>
		<li>Bugfix: Backslashes im Templatecode wurden versehentlich entfernt.</li>
		<li>Daten bezüglich 1&1 aktualisiert.</li>
		<li>Fehlermeldungen beim Versand verbessert.</li>
		<li>Bugfix: bei der Webansicht von manchen Providern wie GMX wurden Links nicht verlinkt wenn es sich nicht um Links mit der kompletten URL handelt. Ab sofort wird immer die komplette URL ergänzt, falls sie fehlt.</li>
		<li>Bugfix: Image Manager URLS beinhalten ein "&". Dieses "&" wurde bisher kodiert und manche Mailprogramme konnten die Bilder dann nicht mehr laden. Jetzt werden alle "&" vor dem Versand zur Sicherheit decodiert.</li>
	</ul>
	<p>3.1.6</p>
	<ul>
		<li>Bugfix: bei der Webansicht von manchen Providern wie GMX wurden Links nicht verlinkt wenn es sich nicht um Links mit der kompletten URL handelt. Ab sofort wird immer die komplette URL ergänzt, falls sie fehlt.</li>
		<li>Aktion beim Abmelden ist jetzt immer "löschen". Die Option den Status auf abgemeldet zu setzen wurde wegen der DSGVO entfernt. ACHTUNG: Nutzer, deren Status abgemeldet (= 2) gesetzt ist werden beim Update auf diese Version gelöscht.</li>
		<li>Option Sprachfallback deaktivieren hinzugefügt (Danke palber!).</li>
		<li>FAQ zum Datenschutz ergänzt.</li>
	</ul>
	<p>3.1.5</p>
	<ul>
		<li>Module: emailobfuscator für E-Mail-Adressen in Formularfelder deaktiviert.</li>
		<li>Module: erneute Anmeldung wenn Datenschutzerklärung noch nicht zugestimmt wurde möglich, wodurch der alte Datensatz aktualisiert wird.</li>
		<li>Feld Datenschutzerklärung akzeptiert im Frontend Formular hinzugefügt. BITTE in den Einstellungen die Übersetzung aktualisieren und in den Einstellungen des D2U Helper Addons den Link für die Datenschutzerklärung und das Impressum festlegen.</li>
		<li>Bugfix: automatische Datenübernahme hat nur mit rex_ Tabellen funktioniert.</li>
		<li>Module: Formularklassen auf YForm und Bootstrap 4 angepasst.</li>
	</ul>
	<p>3.1.4</p>
	<ul>
		<li>Automatische Datenübernahme bei Installation oder Reinstallation wenn Redaxo 4 Tabellen in Datenbank vorhanden sind.</li>
		<li>Bugfix: Anhängeprüfung konnte nach Neuinstallation des Addons zu fatal error führen.</li>
		<li>Bugfix: Abmeldung bei nicht existierender E-Mail-Adresse führte zu Fehler.</li>
		<li>Neues Anmeldemodul für das lediglich die E-Mail-Adresse abgefragt wird.</li>
	</ul>
	<p>3.1.3</p>
	<ul>
		<li>Bugfix: Benutzerliste speichert wieder korrekt.</li>
		<li>Bugfix: Empfänger im Archiv werden wieder korrekt dargestellt.</li>
		<li>Bugfix: Einstellungen Optionen werden vor dem Speichern ausgeklappt,
			damit Pflichtfelder fokussierbar werden.</li>
	</ul>
	<p>3.1.2</p>
	<ul>
		<li>Bugfix: Import scheiterte, wenn Adresse mit Leerstelle endete.</li>
		<li>Bugfix: GruppenIDs wurden seit 3.0.8 nicht mehr importiert.</li>
		<li>Bugfix: Newsletter Voreinstellungen beim ersten Versandschritt werden wieder korrekt geladen.</li>
	</ul>
	<p>3.1.1</p>
	<ul>
		<li>Englisches Backend hinzugefügt.</li>
		<li>Bugfix: Import war seit Version 3.0.8 kaputt.</li>
		<li>Bugfix: Update war in Version 3.1.0 kaputt.</li>
		<li>Bugfix: Aktivierungslink war ohne E-Mail-Adresse.</li>
		<li>Bugfix: SEO42 aus Beispieltemplate entfernt.</li>
	</ul>
	<p>3.1.0: Danke an Alex Platter! Das ist wieder deine Version.</p>
	<ul>
		<li>MailChimp Anbindung.</li>
		<li>Bugfix: Sonderzeichen in Artikelname werden jetzt korrekt in DB gespeichert.</li>
		<li>Bugfix: Wenn während des Versands die Session abbricht wird beim erneuten Login nun der Versandstatus korrekt initialisiert.</li>
		<li>Bugfix: Löschen einer Sprache löscht nun auch entsprechende Archive und setzt Sprache der Benutzer zurück.</li>
	</ul>
	<p>3.0.8: Danke an Alex Platter! Das ist deine Version.</p>
	<ul>
		<li>Italienische Presets hinzugefügt.</li>
		<li>Newsletter Versand Reload JavaScript verbessert.</li>
		<li>SMTP Override.</li>
		<li>BCC Override.</li>
	</ul>
	<p>3.0.7:</p>
	<ul>
		<li>Bugfix: Update von 3.0.x auf 3.0.6 schlug fehl</li>
	</ul>
	<p>3.0.6:</p>
	<ul>
		<li>Beim Versand werden zuletzt versendete Adressen angezeigt.</li>
		<li>Klasse zum Verwalten von Einstellungen in das D2U Helper Addon ausgelagert.</li>
		<li>Komfortable Modulverwaltung mit Updateoption hinzugefügt.</li>
	</ul>
	<p>3.0.5:</p>
	<ul>
		<li>Anzahl Empfänger im Archiv angezeigt.</li>
		<li>Sortierung beim Versand nach E-Mail-Adresse.</li>
	</ul>
	<p>3.0.4:</p>
	<ul>
		<li>Bugfix: Settings textarea field without rex_redactor2 possible.</li>
		<li>Bugfix: Fehler bei Benutzern mit Nachnamen mit '.</li>
		<li>Bugfix: Keine Meldung bei Versand an Gruppe ohne Nutzer behoben.</li>
	</ul>
	<p>3.0.3:</p>
	<ul>
		<li>Bugfix: Einstellungen Fehler wenn Artikel nicht gefunden wird.</li>
		<li>Bugfix: Bei Mehrsprachigkeit wird beim Absender auch die Sprachbezeichnung wieder angezeigt.</li>
	</ul>
	<p>3.0.2:</p>
	<ul>
		<li>Bugfix: Fehler unter PHP 5.5 und 5.6 beim Anzeigen der Einstellungsseitebehoben.</li>
		<li>Bugfix: Fehler beim Anzeigen der Linkmap, wenn noch keine Standardsprache festgelegt ist.</li>
		<li>Bugfix: Bei Benutzern ist die Gruppe jetzt Pflichtfeld. Sonst kann nicht gespeichert werden.</li>
		<li>Bugfix: Versand bricht bei ungüligen Userdaten nicht mehr ab.</li>
		<li>Bugfix: Fehlermeldungen beim Versand werden korrekt angezeigt.</li>
	</ul>
	<p>3.0.1:</p>
	<ul>
		<li>Bugfix: Newsletter nur an aktive Nutzer mailen.</li>
		<li>Optische Korrekturen und Verbesserungen.</li>
		<li>Übersetzungen aufgeräumt.</li>
		<li>Bugfix Einstellungen.</li>
	</ul>
	<p>3.0.0:</p>
	<ul>
		<li>Alte Platzhalter die mit /// beginnen und enden entfernt.</li>
		<li>Portierung auf Redaxo 5.</li>
		<li>Fehler wenn doppelte E-Mail-Adressen im Import vorhanden sind behoben.</li>
		<li>Suche in Benutzerliste wird in Session gespeichert.</li>
		<li>Tabellenengine auf InnoDB umgestellt (Redaxo 5 Standard).</li>
	</ul>
	<p>2.2.3:</p>
	<ul>
		<li>Nutzer mit GruppenID NULL werden in Liste der Benutzer ohne Gruppe angezeigt.</li>
		<li>E-Mail-Adressen konnten mit Leerzeichen gespeichert werden und haben so den Versand blockiert.</li>
		<li>Auf Bootstrap basierendes Beispieltemplate hinzugefügt.</li>
	</ul>
	<p>2.2.2:</p>
	<ul>
		<li>HTMLBody im Archiv nun auch vom Typ Longtext.</li>
		<li>Anzahl Benutzer anzeigen.</li>
	</ul>
	<p>2.2.1:</p>
	<ul>
		<li>Funktion MultiNewsletterUser:initByMail() gibt jetzt bei nicht
		gefundener E-Mail-Adresse korrekt false zurück.</li>
		<li>Anmeldung und Abmeldung verbessert.</li>
		<li>Platzhalter für Links beginnen und enden jetzt mit +++.</li>
	</ul>
	<p>2.2:</p>
	<ul>
		<li>Bessere Dokumentation für Aktivierungslink. Thx @missmissr</li>
		<li>Vor Import werden unnötige Leerzeichen entfernt. Thx @missmissr</li>
		<li>Speichert jetzt auch IPv6 Adressen.</li>
	</ul>
	<p>2.1.1:</p>
	<ul>
		<li>Fallbacksprache in den Einstellungen hatte Fehler wenn nur eine Sprache
			vorhanden war. Thx @missmissr</li>
	</ul>
	<p>2.1.0:</p>
	<ul>
		<li>Auswahl Aktion beim Abmelden: Status auf Löschen oder Abgemeldet setzbar.</li>
		<li>Möglichkeit einer Benachrichtigung per Mail bei bei An- und Abmeldung.</li>
	</ul>
	<p>2.0.0:</p>
	<ul>
		<li>Anpassungen an Redaxo 4.6: Einstellungen werden jetzt im data Verzeichnis gespeichert.</li>
		<li>Übersetzungen können jetzt ohne FTP in den Einstellungen bearbeitet werden.</li>
		<li>Minimal PHP 5.2; Notices entfernt.</li>
		<li>GUI Anpassungen.</li>
		<li>Bestätigungsmail bei Anmeldung</li>
		<li>Benutzerverwaltung: Suchkriterien bleiben beim Verlassen des Formulars erhalten</li>
		<li>Vorbelegungen für Absender bei Gruppen möglich.</li>
		<li>Vorbelegung für Testmails einstellbar</li>
		<li>BCC Versand von Newslettern entfernt.</li>
		<li>Bugfixing</li>
		<li>Objektorientierte Programmierung.</li>
		<li>Versand an mehrere Gruppen gleichzeitig möglich.</li>
		<li>Viele Kleinigkeiten mehr.</li>
	</ul>
	<p>1.4.2:</p>
	<ul>
		<li>Mehrfache Anmeldung wird geblockt (Danke Frood)</li>
	</ul>
	<p>1.4.1:</p>
	<ul>
		<li>Anrede wird korrekt gesetzt.</li>
		<li>Fehler wiederholtes Datenbankupdate wird bei leerer Tabelle behoben.</li>
	</ul>
	<p>1.4:</p>
	<ul>
		<li>Akademischer Grad als zweiten Titel hinzugf&uuml;gt (Danke "steri").</li>
		<li>Probleme mit RexSEO und leading Slash behoben ("/" nach Domainnamen).</li>
	</ul>
	<p>1.3:</p>
	<ul>
		<li>CSV Import hat nun die M&ouml;glichkeit eine Aktion auszuw&auml;hlen: l&ouml;schen, nur neue hinzuf&uuml;gen, &uuml;berschreiben.</li>
		<li>Module werden bei der Installation in Redaxo installiert.</li>
	</ul>
	<p>1.2.5 Bugfixing:</p>
	<ul>
		<li>Hilfe f&uuml;r Sprache de_de aktualisiert.</li>
		<li>Fehler bei Personalisierung der Anrede bei Mehrsprachigkeit behoben.</li>
		<li>GruppenID kann in Spalte send_group im Import mit angegeben werden.</li>
	</ul>
	<p>1.2.4 Bugfixing:<br />
	<ul>
		<li>Mehrfache Anmeldung wird geblockt (Danke Frood)</li>
		<li>Lang Fallback Fehler behoben</li>
	</ul>
	<p>1.2.3 Bugfixing:<br />
	<ul>
		<li>Kompatibilit&auml;t zu Redaxo 4.1 im Ausgabemodul hergestellt.</li>
	</ul>
	<p>1.2.2 Bugfixing:<br />
	<ul>
		<li>Falls max_execution_time in der PHP Config auf 0 gesetzt wird, wird jetzt ein Standardwert gesetzt.</li>
	</ul>
	<p>1.2.1 Bugfixing:<br />
	<ul>
		<li>Fehler im Abmeldungsmodul behoben</li>
	</ul>
	<p>1.2:<br />
	<ul>
		<li>Usertracking verbessert. Gespeichert wird jetzt: Erstellungsdatum und -IP,
			Aktivierungsdatum und -IP, Updatedatum und IP sowie Anmeldeart (Web, Import, Backend)</li>
		<li>Bugfix: ab jetzt k&ouml;nnen auf Mac erzeugte Import CSV Dateien importiert werden.</li>
	</ul>
	<p>1.1.7 Bugfixing und mehr:<br />
	<ul>
		<li>Nach Abmeldung funktioniert die erneute Anmeldung jetzt wieder.</li>
		<li>Es werden keine Abmeldebest&auml;tigungsmails mehr verschickt. Die Abmeldung erfolgt immer sofort.</li>
		<li>Sprachdatei de_de.lang hinzugef&uuml;gt</li>
		<li>kleinen &Uuml;bersetzungsfehler behoben</li>
		<li>In Benutzer&uuml;bersicht Erstellungs- und Aktualisierungsdatum hinzugef&uuml;gt</li>
	</ul>
	<p>1.1.6 Bugfixing:<br />
	<ul>
		<li>Verzeichnis Module enth&auml;lt jetzt die Module f&uuml;r Anmeldung und Abmeldung</li>
		<li>Nutzt jetzt rex_mailer Klasse des PHP Mailer Addons (bisher PHPMailer Klasse).</li>
	</ul>
	<p>1.1.5 Bugfixing:<br />
	<ul>
		<li>Weiteres PHP short_open_tag entfernt.</li>
		<li>Nutzt jetzt einheitlich PHPMailer Addon.</li>
	</ul>
	<p>1.1.4 Bugfixing:<br />
	<ul>
		<li>&Uuml;bersetzungsfehler bei invalid_email behoben.</li>
		<li>Meldung wenn E-Mail-Adresse schon angemeldet ist.</li>
		<li>Backend PHP Einstellung short_open_tag = Off kompatibel.</li>
	</ul>
	<p>1.1.3 Bugfixing:<br />
	<ul>
		<li>Link der Best&auml;tigungsmail wurde im Frontend angezeigt. Jetzt entfernt.</li>
	</ul>
	<p>1.1.2 Bugfixing:</p>
	<ul>
		<li>Erkennt und l&ouml;scht ung&uuml;ltige E-Mail-Adressen w&auml;hrend dem Newsletterversand.</li>
		<li>peichert bei neuen Benutzern auch die Gruppen.</li>
		<li>Fehler beim Speichern von neuen Benutzern ohne Namen behoben</li>
		<li>Wenn w&auml;hrend dem Newsletterversand benutzer ge&auml;ndert werden, wird Versand nicht mehr unterbrochen.</li>
		<li>Sprachfallback Fehler beim Versenden behoben.</li>
	</ul>
	<p>1.1</p>
	<ul>
		<li>Serverlimits und Sprachfallback</li>
	</ul>
	<p>1.0 Ver&ouml;ffentlicht am 20. August 2008 von <a href="http://thomasgoellner.de/" target="_blank">Thomas G&ouml;llner</a>.
		Spätere	Weiterentwicklung von <a href="http://www.design-to-use.de" target="blank">Tobias Krais</a>.</p>
</fieldset>