<?php

$sql = rex_sql::factory();
// Delete tables
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_archive`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_group`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_user`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_sendlist`');

// Remove CronJobs
if (!class_exists('multinewsletter_cronjob_sender')) {
    // Load class in case addon is deactivated
    require_once 'lib/cronjob_sender.php';
}
$cronjob_sender = multinewsletter_cronjob_sender::factory();
if ($cronjob_sender->isInstalled()) {
    $cronjob_sender->delete();
}
if (!class_exists('multinewsletter_cronjob_cleanup')) {
    // Load class in case addon is deactivated
    require_once 'lib/cronjob_cleanup.php';
}
$cronjob_cleanup = multinewsletter_cronjob_cleanup::factory();
if ($cronjob_cleanup->isInstalled()) {
    $cronjob_cleanup->delete();
}
