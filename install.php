<?php

\rex_sql_table::get(\rex::getTable('375_archive'))
    ->ensureColumn(new rex_sql_column('id', 'INT(11) unsigned', false, null, 'auto_increment'))
    ->setPrimaryKey('id')
    ->ensureColumn(new \rex_sql_column('article_id', 'INT(11)', true))
    ->ensureColumn(new \rex_sql_column('clang_id', 'INT(11)', true))
    ->ensureColumn(new \rex_sql_column('subject', 'VARCHAR(191)', true))
    ->ensureColumn(new \rex_sql_column('htmlbody', 'LONGTEXT', true))
    ->ensureColumn(new \rex_sql_column('attachments', 'TEXT', true))
    ->ensureColumn(new \rex_sql_column('recipients', 'LONGTEXT', true))
    ->ensureColumn(new \rex_sql_column('recipients_failure', 'LONGTEXT', true))
    ->ensureColumn(new \rex_sql_column('group_ids', 'TEXT', true))
    ->ensureColumn(new \rex_sql_column('sender_email', 'VARCHAR(191)', true))
    ->ensureColumn(new \rex_sql_column('sender_name', 'VARCHAR(191)', true))
    ->ensureColumn(new \rex_sql_column('reply_to_email', 'VARCHAR(191)', true))
    ->ensureColumn(new \rex_sql_column('setupdate', 'DATETIME', true))
    ->ensureColumn(new \rex_sql_column('sentdate', 'DATETIME', true))
    ->ensureColumn(new \rex_sql_column('sentby', 'VARCHAR(191)', true))
    ->ensureIndex(new rex_sql_index('setupdate', ['setupdate', 'clang_id'], rex_sql_index::UNIQUE))
    ->ensure();

\rex_sql_table::get(\rex::getTable('375_group'))
    ->ensureColumn(new rex_sql_column('id', 'INT(11) unsigned', false, null, 'auto_increment'))
    ->setPrimaryKey('id')
    ->ensureColumn(new \rex_sql_column('name', 'VARCHAR(191)', true))
    ->ensureIndex(new rex_sql_index('name', ['name'], rex_sql_index::UNIQUE))
    ->ensureColumn(new \rex_sql_column('default_sender_email', 'VARCHAR(191)', true))
    ->ensureColumn(new \rex_sql_column('default_sender_name', 'VARCHAR(191)', true))
    ->ensureColumn(new \rex_sql_column('reply_to_email', 'VARCHAR(191)', true))
    ->ensureColumn(new \rex_sql_column('default_article_id', 'INT(11)', true))
    ->ensureColumn(new \rex_sql_column('mailchimp_list_id', 'VARCHAR(100)', true))
    ->ensure();

\rex_sql_table::get(\rex::getTable('375_user'))
    ->ensureColumn(new rex_sql_column('id', 'INT(11) unsigned', false, null, 'auto_increment'))
    ->setPrimaryKey('id')
    ->ensureColumn(new \rex_sql_column('email', 'VARCHAR(191)', true))
    ->ensureIndex(new rex_sql_index('email', ['email'], rex_sql_index::UNIQUE))
    ->ensureColumn(new \rex_sql_column('grad', 'VARCHAR(191)', true))
    ->ensureColumn(new \rex_sql_column('firstname', 'VARCHAR(191)', true))
    ->ensureColumn(new \rex_sql_column('lastname', 'VARCHAR(191)', true))
    ->ensureColumn(new \rex_sql_column('title', 'TINYINT(4)', true))
    ->ensureColumn(new \rex_sql_column('clang_id', 'INT(11)', true))
    ->ensureColumn(new \rex_sql_column('status', 'TINYINT(1)', true))
    ->ensureColumn(new \rex_sql_column('group_ids', 'TEXT', true))
    ->ensureColumn(new \rex_sql_column('mailchimp_id', 'VARCHAR(100)', true))
    ->ensureColumn(new \rex_sql_column('createdate', 'DATETIME', true))
    ->ensureColumn(new \rex_sql_column('createip', 'VARCHAR(45)', true))
    ->ensureColumn(new \rex_sql_column('activationdate', 'DATETIME', true))
    ->ensureColumn(new \rex_sql_column('activationip', 'VARCHAR(45)', true))
    ->ensureColumn(new \rex_sql_column('activationkey', 'VARCHAR(45)', true))
    ->ensureColumn(new \rex_sql_column('updatedate', 'DATETIME', true))
    ->ensureColumn(new \rex_sql_column('updateip', 'VARCHAR(45)', true))
    ->ensureColumn(new \rex_sql_column('subscriptiontype', 'VARCHAR(16)', true))
    ->ensureColumn(new \rex_sql_column('privacy_policy_accepted', 'TINYINT(1)', true))
    ->ensure();

\rex_sql_table::get(\rex::getTable('375_sendlist'))
    ->ensureColumn(new rex_sql_column('archive_id', 'INT(11)', false))
    ->ensureColumn(new \rex_sql_column('user_id', 'INT(11)', false))
    ->setPrimaryKey(['archive_id', 'user_id'])
    ->ensureColumn(new \rex_sql_column('autosend', 'TINYINT(1)'))
    ->ensure();

rex_sql_table::get(rex::getTable('375_archive'))
    ->removeColumn('send_archive_id')
    ->alter();

rex_sql_table::get(rex::getTable('375_group'))
    ->removeColumn('createdate')
    ->removeColumn('updatedate')
    ->alter();

// Standartkonfiguration erstellen
if (!rex_config::has('multinewsletter', 'default_test_email')) {
    rex_config::set('multinewsletter', 'default_test_email', rex::getProperty('ERROR_EMAIL'));
    rex_config::set('multinewsletter', 'default_test_article', rex_article::getSiteStartArticleId());
    rex_config::set('multinewsletter', 'default_test_sprache', rex_config::get('d2u_helper', 'default_lang'));
}

// Update modules
if (class_exists(TobiasKrais\D2UHelper\ModuleManager::class)) {
    $d2u_multinewsletter_modules = [];
    $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-1',
        'MultiNewsletter Anmeldung mit Name und Anrede',
        7);
    $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-2',
        'MultiNewsletter Abmeldung',
        7);
    $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-3',
        'MultiNewsletter Anmeldung nur mit Mail',
        7);
    $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-4',
        'MultiNewsletter YForm Anmeldung',
        9);
    $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-5',
        'MultiNewsletter YForm Abmeldung',
        4);

    $d2u_module_manager = new \TobiasKrais\D2UHelper\ModuleManager($d2u_multinewsletter_modules, '', 'multinewsletter');
    $d2u_module_manager->autoupdate();
}
