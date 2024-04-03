<?php
/**
 * Administrates background send CronJob for MultiNewsletter.
 */
class multinewsletter_cronjob_sender extends TobiasKrais\D2UHelper\ACronJob
{
    /**
     * Create a new instance of object.
     * @return multinewsletter_cronjob_sender CronJob object
     */
    public static function factory()
    {
        $cronjob = new self();
        $cronjob->name = 'MultiNewsletter Sender';
        return $cronjob;
    }

    /**
     * Install CronJob. Its not activated.
     */
    public function install(): void
    {
        $description = 'Sendet ausstehende Newsletter im Hintergrund. Aktiviert und deaktiviert sich automatisch.';
        $php_code = '<?php MultinewsletterNewsletterManager::cronSend(); ?>';
        $interval = '{\"minutes\":\"all\",\"hours\":\"all\",\"days\":\"all\",\"weekdays\":\"all\",\"months\":\"all\"}';
        $activate = false;
        self::save($description, $php_code, $interval, $activate);
    }
}
