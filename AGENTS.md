# MultiNewsletter - Redaxo Addon

A Redaxo 5 CMS addon for managing newsletter subscriptions, sending personalized newsletter emails, and organizing subscribers in groups. Includes Mailchimp integration, cronjob-based sending, subscriber import/export, and multiple frontend signup/unsubscribe modules.

## Tech Stack

- **Language:** PHP >= 8.0
- **CMS:** Redaxo >= 5.10.0
- **Frontend Framework:** Bootstrap 4/5 (via d2u_helper templates)
- **Namespace:** `FriendsOfRedaxo\MultiNewsletter`

## Project Structure

```text
multinewsletter/
├── boot.php               # Addon bootstrap (OUTPUT_FILTER, Mailchimp sync, cleanup hooks)
├── install.php             # Installation (database tables, cronjobs)
├── update.php              # Update handler
├── uninstall.php           # Cleanup (database tables, cronjobs)
├── package.yml             # Addon configuration, version, dependencies
├── README.md
├── assets/                 # CSS, JS, template preview images
├── lang/                   # Backend translations (de_de, en_gb)
├── lib/                    # PHP classes
│   ├── User.php            # Newsletter subscriber model
│   ├── Newsletter.php      # Newsletter archive and sending
│   ├── NewsletterManager.php  # Send orchestration, cronjob sending, auto-cleanup
│   ├── Group.php           # Subscriber group model
│   ├── Userlist.php        # User listing and CSV export
│   ├── Mailchimp.php       # Mailchimp API v3 integration
│   ├── CronjobSender.php   # Cronjob for background sending
│   ├── CronjobCleanup.php  # Cronjob for weekly cleanup
│   ├── Module.php          # Module definitions and revisions
│   └── deprecated_classes.php  # Backward compatibility (since 3.6.0)
├── modules/                # 5 module variants in group 80
│   └── 80/
│       ├── 1/              # Signup with name and salutation
│       ├── 2/              # Unsubscribe
│       ├── 3/              # Signup with email only
│       ├── 4/              # YForm signup
│       └── 5/              # YForm unsubscribe
├── pages/                  # Backend pages
│   ├── index.php           # Main page + router
│   ├── newsletter.php      # Newsletter sending
│   ├── user.php            # Subscriber management
│   ├── groups.php          # Group management
│   ├── archive.php         # Newsletter archive
│   ├── import.php          # Subscriber import
│   ├── settings.settings.php  # Addon settings
│   ├── settings.export.php    # Settings export
│   ├── settings.import.php    # Settings import
│   ├── help.changelog.php     # Changelog
│   ├── help.faq.php           # FAQ
│   ├── help.import.php        # Import help
│   ├── help.module.php        # Module help
│   ├── help.templates.php     # Template help
│   ├── help.updatehinweise.php # Update notes
│   └── help.versand.php       # Sending help
├── snippets/
│   └── yform_manager_tableset_user.json  # YForm tableset for user table
└── templates/
    └── template_01.php     # HTML email template (header/footer styling)
```

## Coding Conventions

- **Namespace:** `FriendsOfRedaxo\MultiNewsletter` for all classes
- **Deprecated:** `MultinewsletterGroup`, `MultinewsletterUser`, etc. aliases (since 3.6.0)
- **Naming:** camelCase for variables, PascalCase for classes
- **Indentation:** 4 spaces in PHP classes, tabs in module files
- **Comments:** English comments only
- **Backend labels:** Use `rex_i18n::msg()` with keys from `lang/` files

## Key Classes

| Class | Description |
| ----- | ----------- |
| `User` | Newsletter subscriber: email, name, title, degree, status (inactive/active/unverified), group memberships, activation, Mailchimp sync, CSV import |
| `Newsletter` | Newsletter archive: read article content (via socket or REDAXO), personalize, URL correction, send via PHPMailer, attachments, BCC, SMTP |
| `NewsletterManager` | Send orchestration: prepare send lists, step-by-step sending, cronjob sending, auto-cleanup (archive recipients after 4 weeks, unverified users) |
| `Group` | Subscriber group: name, default sender, reply-to, default article, Mailchimp list ID |
| `Userlist` | User listing: count/load all users, CSV export |
| `Mailchimp` | Mailchimp API v3 integration: list management, user subscription/unsubscription |
| `CronjobSender` | Cronjob for background newsletter sending (extends `ACronJob`) |
| `CronjobCleanup` | Cronjob for weekly cleanup (extends `ACronJob`) |
| `Module` | Module definitions and revision numbers for 5 modules |

## Database Tables

| Table | Description |
| ----- | ----------- |
| `rex_375_user` | Subscribers: email (unique), name, title, language, status, groups, Mailchimp ID, activation key, privacy, IP tracking |
| `rex_375_group` | Subscriber groups: name (unique), sender defaults, reply-to, default article, Mailchimp list ID |
| `rex_375_archive` | Newsletter archive: article, language, subject, HTML body (base64), attachments, recipients, sender, date |
| `rex_375_sendlist` | Send list: archive ID + user ID (PK), autosend flag, scheduled send date |

## Architecture

### Extension Points (registered)

| Extension Point | Location | Purpose |
| --------------- | -------- | ------- |
| `OUTPUT_FILTER` | boot.php (frontend) | Replaces placeholder variables for personalized newsletter links |
| `REX_FORM_SAVED` | boot.php (backend) | Mailchimp sync when user is edited |
| `CLANG_DELETED` | boot.php (backend) | Cleans up user/archive/settings when a language is deleted |
| `REX_YFORM_SAVED` | boot.php (backend) | Sets `subscriptiontype=backend` for new YForm users |
| `ART_PRE_DELETED` | boot.php (backend) | Prevents deletion of articles used by the addon |

### Extension Points (own)

| Extension Point | Location | Purpose |
| --------------- | -------- | ------- |
| `multinewsletter.userActivated` | User.php | Triggered when a user is activated |
| `multinewsletter.preSend` | User.php | Before sending an activation email |
| `multinewsletter.replaceVars` | Newsletter.php | Extend placeholder replacement in newsletters |

### Subscriber Status

| Status | Value | Description |
| ------ | ----- | ----------- |
| Inactive | 0 | Unsubscribed |
| Active | 1 | Verified and active |
| Unverified | 2 | Awaiting email verification |

### Modules

5 module variants in group 80:

| Module | Name | Description |
| ------ | ---- | ----------- |
| 80-1 | Anmeldung mit Name und Anrede | Signup with name and salutation |
| 80-2 | Abmeldung | Unsubscribe |
| 80-3 | Anmeldung nur mit Mail | Signup with email only |
| 80-4 | YForm Anmeldung | YForm-based signup |
| 80-5 | YForm Abmeldung | YForm-based unsubscribe |

#### Module Versioning

Each module has a revision number defined in `lib/Module.php` inside the `getModules()` method. When a module is changed:

1. Add a changelog entry in `pages/help.changelog.php` describing the change.
2. Increment the module's revision number in `Module::getModules()` by one.

**Important:** The revision only needs to be incremented **once per release**, not per commit. Check the changelog: if the version number is followed by `-DEV`, the release is still in development and no additional revision bump is needed.

### Cronjobs

| Cronjob | Schedule | Purpose |
| ------- | -------- | ------- |
| `CronjobSender` | Automatic (activates/deactivates as needed) | Background newsletter sending in batches |
| `CronjobCleanup` | Weekly | Deletes archive recipients after 4 weeks, removes unverified users |

### Mailchimp Integration

- API v3 via `Mailchimp` class (singleton pattern)
- Syncs subscribers bidirectionally between REDAXO and Mailchimp lists
- Each group can be mapped to a Mailchimp list ID

## Dependencies

| Package | Version | Purpose |
| ------- | ------- | ------- |
| `d2u_helper` | >= 1.14.0 | Backend/frontend helpers, module manager, cronjob base class |
| `phpmailer` | >= 2.0.1 | Email sending |

## Multi-language Support

- **Backend:** de_de, en_gb

## Versioning

This addon follows [Semantic Versioning](https://semver.org/). The version number is maintained in `package.yml`. During development, the changelog uses a `-DEV` suffix.

## Changelog

The changelog is located in `pages/help.changelog.php`.
