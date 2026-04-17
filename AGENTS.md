# MultiNewsletter - Agent Notes

Nur projektspezifische Regeln, die für KI-Arbeit relevant sind.

## Kernregeln

- Namespace für Addon-Klassen: `FriendsOfRedaxo\MultiNewsletter`
- Einrückung: 4 Spaces in PHP-Klassen
- Kommentare nur auf Englisch
- Backend-Labels immer über `rex_i18n::msg()` mit Keys aus `lang/`

## Wichtige Projekthinweise

- Backend-Translation-Keys müssen in allen Sprachdateien unter `lang/` synchron bleiben. Aktuell: `de_de`, `en_gb`, `nl_nl`.
- Wenn Module geändert werden, Changelog in `pages/help.changelog.php` prüfen oder aktualisieren und Revisionsstände nur einmal pro Release erhöhen.
- In Changelog-Dateien, AGENTS.md und README.md sind Umlaute erlaubt und müssen nicht auf ASCII umgeschrieben werden.

## Pflege

- Diese Datei kurz und handlungsorientiert halten.
- Neue Einträge nur aufnehmen, wenn sie wiederkehrende Stolperfallen, verbindliche Projektkonventionen oder agentenrelevante Workflows betreffen.
