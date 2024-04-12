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

// use path relative to __DIR__ to get correct path in update temp dir
$this->includeFile(__DIR__.'/install.php'); /** @phpstan-ignore-line */

// 3.1.6 GDPR update
if (rex_config::has('multinewsletter', 'unsubscribe_action')) {
    rex_config::remove('multinewsletter', 'unsubscribe_action');
}
if (rex_config::has('multinewsletter', 'default_test_article_name')) {
    rex_config::remove('multinewsletter', 'default_test_article_name');
}