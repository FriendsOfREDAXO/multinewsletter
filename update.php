<?php

$sql = rex_sql::factory();
// Datenbankengine auf Redaxo Standard umstellen
$sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_archive ENGINE = INNODB;');
$sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_group ENGINE = INNODB;');
$sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_user ENGINE = INNODB;');

// CHANGE primary keys to `id`
if (rex_sql_table::get(rex::getTable('375_user'))->hasColumn('user_id')) {
    $sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_user CHANGE `user_id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;');
}
if (rex_sql_table::get(rex::getTable('375_group'))->hasColumn('group_id')) {
    $sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_group CHANGE `group_id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;');
}
if (rex_sql_table::get(rex::getTable('375_archive'))->hasColumn('archive_id')) {
    $sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_archive CHANGE `archive_id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;');
}

// Add soft_bounce_count column to user table for bounce management
if (!rex_sql_table::get(rex::getTable('375_user'))->hasColumn('soft_bounce_count')) {
    $sql->setQuery('ALTER TABLE ' . rex::getTablePrefix() . '375_user ADD `soft_bounce_count` INT(11) UNSIGNED NOT NULL DEFAULT 0');
}

// Create bounces table for bounce logging
if (!rex_sql_table::get(rex::getTablePrefix() . '375_bounces')) {
    $sql->setQuery('
        CREATE TABLE IF NOT EXISTS ' . rex::getTablePrefix() . '375_bounces (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) UNSIGNED NOT NULL,
            `bounce_type` ENUM("hard_bounces", "soft_bounces", "spam_complaints") NOT NULL,
            `subject` VARCHAR(255) NOT NULL DEFAULT "",
            `body_excerpt` TEXT,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `bounce_type` (`bounce_type`),
            KEY `created_at` (`created_at`),
            FOREIGN KEY (`user_id`) REFERENCES ' . rex::getTablePrefix() . '375_user (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ');
}

// use path relative to __DIR__ to get correct path in update temp dir
$this->includeFile(__DIR__.'/install.php'); /** @phpstan-ignore-line */

// 3.1.6 GDPR update
if (rex_config::has('multinewsletter', 'unsubscribe_action')) {
    rex_config::remove('multinewsletter', 'unsubscribe_action');
}
if (rex_config::has('multinewsletter', 'default_test_article_name')) {
    rex_config::remove('multinewsletter', 'default_test_article_name');
}