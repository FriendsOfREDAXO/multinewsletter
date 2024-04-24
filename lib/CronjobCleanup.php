<?php

namespace FriendsOfRedaxo\MultiNewsletter;

/**
 * Administrates background send CronJob for MultiNewsletter.
 */
class CronjobCleanup extends \TobiasKrais\D2UHelper\ACronJob
{
    /**
     * Create a new instance of object.
     * @return self CronJob object
     */
    public static function factory()
    {
        $cronjob = new self();
        $cronjob->name = 'MultiNewsletter CleanUp';
        return $cronjob;
    }

    /**
     * Install CronJob. Its also activated.
     */
    public function install(): void
    {
        $description = 'Ersetzt Empfängeradressen in Archiven die älter als 4 Wochen sind. Außerdem werden nicht aktivierte Abonnenten nach 4 Wochen gelöscht.';
        $php_code = '<?php FriendsOfRedaxo\MultiNewsletter\NewsletterManager::autoCleanup(); ?>';
        $interval = '{\"minutes\":[0],\"hours\":[0],\"days\":\"all\",\"weekdays\":[1],\"months\":\"all\"}';
        self::save($description, $php_code, $interval);
    }
}
