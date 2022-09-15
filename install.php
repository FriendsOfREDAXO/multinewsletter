<?php
$sql = rex_sql::factory();
if (rex_sql_table::get(rex::getTable('375_archive'))->hasColumn('archive_id')) {
	// Migrate Redaxo 4 tables
	$sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_archive` CHANGE `archive_id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive` ADD `article_id` INT(11) NOT NULL AFTER `id`;
		UPDATE `' . rex::getTablePrefix() . '375_archive` SET `clang_id` = (`clang_id` + 1);
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive` CHANGE `htmlbody` `htmlbody` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive` ADD `attachments` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `htmlbody`;
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive` ADD `recipients_failure` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `recipients`;
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive`
			ADD COLUMN `setupdate_new` datetime DEFAULT NULL AFTER `setupdate`,
			ADD COLUMN `sentdate_new` datetime DEFAULT NULL AFTER `sentdate`;
		UPDATE `' . rex::getTablePrefix() . '375_archive`
			SET `setupdate_new` = FROM_UNIXTIME(`setupdate`),
				`sentdate_new` = FROM_UNIXTIME(`sentdate`);
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive` DROP INDEX setupdate;
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive`
			DROP `setupdate`,
			DROP `sentdate`;
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive` CHANGE `setupdate_new` `setupdate` DATETIME NOT NULL;
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive` CHANGE `sentdate_new` `sentdate` DATETIME NOT NULL;
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive`
			ADD UNIQUE KEY `setupdate` (`setupdate`,`clang_id`);	

		ALTER TABLE `' . rex::getTablePrefix() . '375_group` CHANGE `group_id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
		ALTER TABLE `' . rex::getTablePrefix() . '375_group` ADD `mailchimp_list_id` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `default_article_id`;

		ALTER TABLE `' . rex::getTablePrefix() . '375_user` CHANGE `user_id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user` ADD `mailchimp_id` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `send_archive_id`;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user`
			ADD COLUMN `createdate_new` datetime NULL DEFAULT NULL AFTER `createdate`,
			ADD COLUMN `activationdate_new` datetime NULL DEFAULT NULL AFTER `activationdate`,
			ADD COLUMN `updatedate_new` datetime NULL DEFAULT NULL AFTER `updatedate`;
		UPDATE `' . rex::getTablePrefix() . '375_user`
			SET `createdate_new` = FROM_UNIXTIME(`createdate`),
				`activationdate_new` = FROM_UNIXTIME(`activationdate`),
				`updatedate_new` = FROM_UNIXTIME(`updatedate`);
		ALTER TABLE `' . rex::getTablePrefix() . '375_user`
			DROP `createdate`,
			DROP `activationdate`,
			DROP `updatedate`;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user` CHANGE `createdate_new` `createdate` DATETIME NULL DEFAULT NULL;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user` CHANGE `activationdate_new` `activationdate` DATETIME NULL DEFAULT NULL;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user` CHANGE `updatedate_new` `updatedate` DATETIME NULL DEFAULT NULL;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user` ADD `privacy_policy_accepted` TINYINT(1) NOT NULL DEFAULT 0 AFTER `activationkey`;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user` CHANGE `activationkey` `activationkey` VARCHAR(45) NULL DEFAULT NULL;
		UPDATE `' . rex::getTablePrefix() . '375_user` SET `clang_id` = (`clang_id` + 1);

		CREATE TABLE IF NOT EXISTS '. rex::getTablePrefix() .'375_sendlist (
			`archive_id` int(11) unsigned NOT NULL,
			`user_id` int(11) unsigned NOT NULL,
			`autosend` tinyint(1) DEFAULT 0,
			PRIMARY KEY (archive_id, user_id)
		) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;
		INSERT INTO `' . rex::getTablePrefix() . '375_sendlist` (`archive_id`, `user_id`) "
			. "SELECT `send_archive_id`, `id` FROM `' . rex::getTablePrefix() . '375_user` WHERE `send_archive_id` > 0;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user` DROP `send_archive_id`;');
	$sql->setQuery('ALTER TABLE ' . rex::getTablePrefix() . '375_archive ENGINE = INNODB;');
	$sql->setQuery('ALTER TABLE ' . rex::getTablePrefix() . '375_group ENGINE = INNODB;');
	$sql->setQuery('ALTER TABLE ' . rex::getTablePrefix() . '375_user ENGINE = INNODB;');
}
else {
	// Create
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
    ->ensureColumn(new \rex_sql_column('priority', 'INT(11)', true))
    ->ensureColumn(new \rex_sql_column('updatedate', 'DATETIME'))
    ->ensure();
	$sql->setQuery('CREATE TABLE IF NOT EXISTS `' . rex::getTablePrefix() . '375_archive` (
		`id` int(11) unsigned NOT NULL auto_increment,
		`article_id` int(11) NOT NULL,
		`clang_id` int(11) NOT NULL,
		`subject` varchar(191) NOT NULL,
		`htmlbody` longtext NOT NULL,
		`attachments` text NULL DEFAULT NULL,
		`recipients` longtext NULL DEFAULT NULL,
		`recipients_failure` longtext NULL DEFAULT NULL,
		`group_ids` text NOT NULL,
		`sender_email` varchar(191) NOT NULL,
		`sender_name` varchar(191) NOT NULL,
		`reply_to_email` varchar(191) NOT NULL,
		`setupdate` DATETIME NULL DEFAULT NULL,
		`sentdate` DATETIME NULL DEFAULT NULL,
		`sentby` varchar(191) NOT NULL,
	PRIMARY KEY(`id`),
	UNIQUE KEY `setupdate` (`setupdate`, `clang_id`)
	) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;');
	$sql->setQuery('CREATE TABLE IF NOT EXISTS `' . rex::getTablePrefix() . '375_group` (
		`id` int(11) unsigned NOT NULL auto_increment,
		`name` varchar(191) NOT NULL,
		`default_sender_email` varchar(191) NOT NULL,
		`default_sender_name` varchar(191) NOT NULL,
		`reply_to_email` varchar(191) NOT NULL,
		`default_article_id` int(11) unsigned NOT NULL,
		`mailchimp_list_id` varchar(100) NULL,
	PRIMARY KEY(`id`),
	UNIQUE KEY `name` (`name`)
	) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;');
	$sql->setQuery('CREATE TABLE IF NOT EXISTS `' . rex::getTablePrefix() . '375_user` (
		`id` int(11) unsigned NOT NULL auto_increment,
		`email` varchar(191) NOT NULL,
		`grad` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`firstname` varchar(191) NOT NULL,
		`lastname` varchar(191) NOT NULL,
		`title` tinyint(4) NOT NULL,
		`clang_id` int(11) NOT NULL,
		`status` tinyint(1) NOT NULL,
		`group_ids` text NOT NULL,
		`mailchimp_id` varchar(100) NULL,
		`createdate` DATETIME NULL DEFAULT NULL,
		`createip` varchar(45) NOT NULL,
		`activationdate` DATETIME NULL DEFAULT NULL,
		`activationip` varchar(45) NOT NULL,
		`activationkey` varchar(45) NOT NULL,
		`updatedate` DATETIME NULL DEFAULT NULL,
		`updateip` varchar(45) NOT NULL,
		`subscriptiontype` varchar(16) NOT NULL,
		`privacy_policy_accepted` TINYINT(1) NOT NULL DEFAULT 0,
	PRIMARY KEY(`id`),
	UNIQUE KEY `email` (`email`)
	) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;');
	$sql->setQuery("CREATE TABLE IF NOT EXISTS ". rex::getTablePrefix() ."375_sendlist (
		`archive_id` int(11) unsigned NOT NULL,
		`user_id` int(11) unsigned NOT NULL,
		`autosend` tinyint(1) DEFAULT 0,
		PRIMARY KEY (archive_id, user_id)
	) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;");
}

// Standartkonfiguration erstellen
if (!$this->hasConfig()) {
    $this->setConfig('sender', '');
    $this->setConfig('link', 0);
    $this->setConfig('link_abmeldung', 0);
    $this->setConfig('max_mails', 15);
    $this->setConfig('versandschritte_nacheinander', 20);
    $this->setConfig('sekunden_pause', 305);
    $this->setConfig('lang_fallback', 1);
    $this->setConfig('default_test_anrede', 0);
    $this->setConfig('default_test_email', rex::getProperty('ERROR_EMAIL'));
    $this->setConfig('default_test_vorname', 'Max');
    $this->setConfig('default_test_nachname', 'Mustermann');
    $this->setConfig('default_test_article', rex_article::getSiteStartArticleId());
    $this->setConfig('default_test_article_name', '');
    $this->setConfig('default_test_sprache', $default_clang_id);
    $this->setConfig('subscribe_meldung_email', '');
}