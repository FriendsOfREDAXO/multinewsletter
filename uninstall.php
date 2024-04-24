<?php

$sql = rex_sql::factory();
// Delete tables
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_archive`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_group`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_user`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_sendlist`');

// Remove CronJobs
if (!class_exists(FriendsOfRedaxo\MultiNewsletter\CronjobSender::class)) {
    // Load class in case addon is deactivated
    require_once 'lib/CronjobSender.php';
}
$cronjob_sender = FriendsOfRedaxo\MultiNewsletter\CronjobSender::factory();
if ($cronjob_sender->isInstalled()) {
    $cronjob_sender->delete();
}
if (!class_exists(FriendsOfRedaxo\MultiNewsletter\CronjobCleanup::class)) {
    // Load class in case addon is deactivated
    require_once 'lib/CronjobCleanup.php';
}
$cronjob_cleanup = FriendsOfRedaxo\MultiNewsletter\CronjobCleanup::factory();
if ($cronjob_cleanup->isInstalled()) {
    $cronjob_cleanup->delete();
}
