# MultiNewsletter - Agent Notes

Rules only. Short. Actionable.

## Core Rules

- Namespace: `FriendsOfRedaxo\MultiNewsletter`
- PHP classes: 4 spaces
- Comments only in English
- Backend labels always via `rex_i18n::msg()` with keys from `lang/`

## When Changing

- Keep backend translation keys in sync across all files under `lang/`
- For module changes, check or update changelog in `pages/help.changelog.php`
- Raise revision states only once per release
- If target version in changelog already has `-DEV`: do not raise again in same phase
- Use real umlauts in changelog files, AGENTS.md, and README.md

## Maintenance

- Keep only recurring pitfalls, fixed conventions, and agent-relevant workflows here
