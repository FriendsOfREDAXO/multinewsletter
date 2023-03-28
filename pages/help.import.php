<fieldset>
	<legend>MultiNewsletter Import von AddOn-Einstellungen</legend>
	<p class="mb-5"><strong>Verwechslungsgefahr:</strong> Im Gegensatz zum Newsletter-Import mittels CSV-Datei werden die Einstellungen dieses AddOns im JSON-Format exportiert bzw. importiert!</p>
	<legend>MultiNewsletter Import von Benutzer/Gruppen</legend>
	<p>Die wichtigsten Anweisungen für einen erfolgreichen Import einer CSV Datei:</p>
	<ul>
		<li>Feldtrenner muss ein Semikolon (;) sein, KEIN Komma (,)!</li>
		<li>Zeichensatz der Datei sollte UTF-8 sein um Probleme mit Umlauten zu vermeiden.</li>
		<li>Es ist kein Texttrenner vorgesehen.</li>
		<li>Die erste Zeile enthält die Spaltenbezeichnungen. Folgende Spalten sind zulässig:
			<ul>
				<li>email: enthält die E-Mail-Adresse</li>
				<li>grad: enthält den akademischen Grad</li>
				<li>firstname: Vorname</li>
				<li>lastname: Nachname</li>
				<li>title: Anrede - entweder 0 für männlich oder 1 für weiblich</li>
				<li>clang_id: ID der Sprache in Redaxo, der der Nutzer zugeordnet werden soll.
					Exisitiert die Sprache nicht, wird der Nutzer der Fallbacksprache aus den
					Einstellungen zugeordnet. Ist diese nicht gesetzt wird der Nutzer der
					ersten Sprache in Redaxo zugeordnet.</li>
				<li>status: 0 für inaktiv, 1 für aktiv. Ist die Spalte nicht angegeben,
					sind importierte Nutzer inaktiv</li>
				<li>createip: IP Adresse von der aus der Datensatz erstellt wurde.</li>
				<li>group_ids: IDs der Gruppen aus Multinewsletter, denen der Nutzer
					zugeordnet werden soll. Wenn mehrere Gruppen, bitte mit | trennen.</li>
			</ul>
		</li>
		<li>Die Reihenfolge der Spalten spielt keine Rolle.</li>
	</ul>
	<p>Sie können in der Benutzerverwaltung einen Export erstellen und diesen als
		Vorlage verwenden. Bitte beachten Sie, dass der dort erstellte Export weitere
		Spalten aufweist, die nicht importiert werden.</p>
</fieldset>
