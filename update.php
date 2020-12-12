<?php
// Update modules
if(class_exists('D2UModuleManager')) {
	$d2u_multinewsletter_modules = [];
	$d2u_multinewsletter_modules[] = new D2UModule("80-1",
		"MultiNewsletter Anmeldung mit Name und Anrede",
		6);
	$d2u_multinewsletter_modules[] = new D2UModule("80-2",
		"MultiNewsletter Abmeldung",
		7);
	$d2u_multinewsletter_modules[] = new D2UModule("80-3",
		"MultiNewsletter Anmeldung nur mit Mail",
		6);
	$d2u_multinewsletter_modules[] = new D2UModule("80-4",
		"MultiNewsletter YForm Anmeldung",
		2);
	$d2u_multinewsletter_modules[] = new D2UModule("80-5",
		"MultiNewsletter YForm Abmeldung",
		2);

	$d2u_module_manager = new D2UModuleManager($d2u_multinewsletter_modules, "", "multinewsletter");
	$d2u_module_manager->autoupdate();
}

$sql = rex_sql::factory();

// Datenbankengine auf Redaxo Standard umstellen
$sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_archive ENGINE = INNODB;');
$sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_group ENGINE = INNODB;');
$sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_user ENGINE = INNODB;');


rex_sql_table::get(rex::getTable('375_group'))
	->ensureColumn(new \rex_sql_column('mailchimp_list_id', 'varchar(100)', true, null))
	->ensureColumn(new \rex_sql_column('reply_to_email', 'varchar(191)', true, null))
	->alter();
rex_sql_table::get(rex::getTable('375_user'))
	->ensureColumn(new \rex_sql_column('mailchimp_id', 'varchar(100)', true, null))
	->alter();
rex_sql_table::get(rex::getTable('375_archive'))
	->ensureColumn(new \rex_sql_column('attachments', 'text', true, null))
	->ensureColumn(new \rex_sql_column('article_id', 'int'))
	->ensureColumn(new \rex_sql_column('reply_to_email', 'varchar(191)', true, null))
	->alter();

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

// change date format
if (rex_sql_table::get(rex::getTable('375_user'))->getColumn('createdate')->getType() != 'datetime') {
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_user` ADD `_createdate` DATETIME NULL DEFAULT NULL AFTER `createdate`;');
    $sql->setQuery('UPDATE `' . rex::getTablePrefix() . '375_user` SET _createdate = DATE_FORMAT(FROM_UNIXTIME(`createdate`), "%Y-%m-%d %H:%i:%s");');
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_user` DROP `createdate`;');
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_user` CHANGE `_createdate` `createdate` DATETIME NULL DEFAULT NULL');

    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_user` ADD `_updatedate` DATETIME NULL DEFAULT NULL AFTER `updatedate`;');
    $sql->setQuery('UPDATE `' . rex::getTablePrefix() . '375_user` SET _updatedate = DATE_FORMAT(FROM_UNIXTIME(`updatedate`), "%Y-%m-%d %H:%i:%s");');
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_user` DROP `updatedate`;');
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_user` CHANGE `_updatedate` `updatedate` DATETIME NULL DEFAULT NULL');

    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_user` ADD `_activationdate` DATETIME NULL DEFAULT NULL AFTER `activationdate`;');
    $sql->setQuery('UPDATE `' . rex::getTablePrefix() . '375_user` SET _activationdate = DATE_FORMAT(FROM_UNIXTIME(`activationdate`), "%Y-%m-%d %H:%i:%s");');
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_user` DROP `activationdate`;');
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_user` CHANGE `_activationdate` `activationdate` DATETIME NULL DEFAULT NULL');

    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_group` ADD `_createdate` DATETIME NULL DEFAULT NULL AFTER `createdate`;');
    $sql->setQuery('UPDATE `' . rex::getTablePrefix() . '375_group` SET _createdate = DATE_FORMAT(FROM_UNIXTIME(`createdate`), "%Y-%m-%d %H:%i:%s");');
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_group` DROP `createdate`;');
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_group` CHANGE `_createdate` `createdate` DATETIME NULL DEFAULT NULL');

    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_group` ADD `_updatedate` DATETIME NULL DEFAULT NULL AFTER `updatedate`;');
    $sql->setQuery('UPDATE `' . rex::getTablePrefix() . '375_group` SET _updatedate = DATE_FORMAT(FROM_UNIXTIME(`updatedate`), "%Y-%m-%d %H:%i:%s");');
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_group` DROP `updatedate`;');
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_group` CHANGE `_updatedate` `updatedate` DATETIME NULL DEFAULT NULL');

    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_archive` DROP INDEX `setupdate`;');

    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_archive` ADD `_setupdate` DATETIME NULL DEFAULT NULL AFTER `setupdate`;');
    $sql->setQuery('UPDATE `' . rex::getTablePrefix() . '375_archive` SET _setupdate = DATE_FORMAT(FROM_UNIXTIME(`setupdate`), "%Y-%m-%d %H:%i:%s");');
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_archive` DROP `setupdate`;');
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_archive` CHANGE `_setupdate` `setupdate` DATETIME NULL DEFAULT NULL');

    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_archive` ADD `_sentdate` DATETIME NULL DEFAULT NULL AFTER `sentdate`;');
    $sql->setQuery('UPDATE `' . rex::getTablePrefix() . '375_archive` SET _sentdate = DATE_FORMAT(FROM_UNIXTIME(`sentdate`), "%Y-%m-%d %H:%i:%s");');
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_archive` DROP `sentdate`;');
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_archive` CHANGE `_sentdate` `sentdate` DATETIME NULL DEFAULT NULL');

    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_archive` ADD UNIQUE(`setupdate`, `clang_id`);');
}

// 3.1.5 Update database
$sql->setQuery("SHOW COLUMNS FROM ". \rex::getTablePrefix() ."375_user LIKE 'privacy_policy_accepted';");
if($sql->getRows() == 0) {
	$sql->setQuery("ALTER TABLE `". \rex::getTablePrefix() ."375_user` ADD `privacy_policy_accepted` TINYINT(1) NOT NULL DEFAULT 0 AFTER `activationkey`;");
}
// 3.2.0 Update database
// Enlarge Activation Key field
$sql->setQuery("ALTER TABLE `" . rex::getTablePrefix() . "375_user` CHANGE `activationkey` `activationkey` VARCHAR(45) NULL DEFAULT NULL;");
// Outsource send_archive_id in extra table
$sql->setQuery("CREATE TABLE IF NOT EXISTS ". rex::getTablePrefix() ."375_sendlist (
	`archive_id` int(11) unsigned NOT NULL,
	`user_id` int(11) unsigned NOT NULL,
	`autosend` tinyint(1) DEFAULT 0,
	PRIMARY KEY (archive_id, user_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;");
$sql->setQuery("SHOW COLUMNS FROM ". \rex::getTablePrefix() ."375_user LIKE 'send_archive_id';");
if($sql->getRows() > 0) {
	$sql->setQuery("SELECT * FROM `" . rex::getTablePrefix() . "375_user` WHERE `send_archive_id` > 0;");
	if($sql->getRows() > 0) {
		$sql->setQuery("INSERT INTO `" . rex::getTablePrefix() . "375_sendlist` (`archive_id`, `user_id`) "
			. "SELECT `send_archive_id`, `id` FROM `" . rex::getTablePrefix() . "375_user` WHERE `send_archive_id` > 0;");
	}
    $sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_user` DROP `send_archive_id`;');
}
rex_sql_table::get(rex::getTable('375_archive'))->ensureColumn(new \rex_sql_column('recipients_failure', 'longtext', true, null))->alter();

// Update modules
if(class_exists('D2UModuleManager') && class_exists('D2UMultiNewsletterModules')) {
	$d2u_module_manager = new D2UModuleManager(D2UMultiNewsletterModules::getD2UMultiNewsletterModules(), "modules/", "multinewsletter");
	$d2u_module_manager->autoupdate();
}

// 3.1.6 GDPR update
if($this->hasConfig('unsubscribe_action')) {
	$this->removeConfig('unsubscribe_action');
}
$sql->setQuery('DELETE FROM ' . rex::getTablePrefix() . '375_user WHERE `status` = 2;');

// 3.2.4
$sql->setQuery("ALTER TABLE `". rex::getTablePrefix() ."375_group` CHANGE `name` `name` VARCHAR(191) NULL DEFAULT NULL;");
$sql->setQuery("ALTER TABLE `". rex::getTablePrefix() ."375_user` CHANGE `email` `email` VARCHAR(191) NULL DEFAULT NULL;");
$sql->setQuery("ALTER TABLE `". rex::getTablePrefix() ."375_archive` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
$sql->setQuery("ALTER TABLE `". rex::getTablePrefix() ."375_group` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
$sql->setQuery("ALTER TABLE `". rex::getTablePrefix() ."375_user` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
$sql->setQuery("ALTER TABLE `". rex::getTablePrefix() ."375_sendlist` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");