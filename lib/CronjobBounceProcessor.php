<?php

namespace FriendsOfRedaxo\MultiNewsletter;

/**
 * Cronjob for processing bounced emails via IMAP
 * 
 * @author MultiNewsletter Team
 */
class CronjobBounceProcessor extends \TobiasKrais\D2UHelper\ACronJob
{
    /**
     * Create a new instance of bounce processor cronjob
     * @return self CronJob object
     */
    public static function factory()
    {
        $cronjob = new self();
        $cronjob->name = 'MultiNewsletter Bounce Processor';
        return $cronjob;
    }

    /**
     * Install bounce processor cronjob
     */
    public function install(): void
    {
        $description = 'Verarbeitet Bounce-E-Mails über IMAP und verwaltet automatisch Benutzerabonnements basierend auf E-Mail-Zustellungsfehlern.';
        $php_code = '<?php \\\\\\\\FriendsOfRedaxo\\\\\\\\MultiNewsletter\\\\\\\\CronjobBounceProcessor::processBounces(); ?>';
        $interval = '{"minutes":[0,30],"hours":"all","days":"all","weekdays":"all","months":"all"}'; // Every 30 minutes
        $activate = false; // Not activated by default
        self::save($description, $php_code, $interval, $activate);
    }

    /**
     * Process bounced emails
     */
    public static function processBounces(): void
    {
        try {
            $bounceHandler = new BounceHandler();
            $results = $bounceHandler->processBounces();
            
            if ($results['processed'] > 0) {
                echo "Bounce processing completed:\n";
                echo "- Total processed: " . $results['processed'] . "\n";
                echo "- Hard bounces: " . $results['hard_bounces'] . "\n";
                echo "- Soft bounces: " . $results['soft_bounces'] . "\n";
                echo "- Spam complaints: " . $results['spam_complaints'] . "\n";
                
                if (!empty($results['errors'])) {
                    echo "Errors:\n";
                    foreach ($results['errors'] as $error) {
                        echo "- " . $error . "\n";
                    }
                }
            } else {
                echo "No bounced emails to process.\n";
            }
            
        } catch (\Exception $e) {
            echo "Error processing bounces: " . $e->getMessage() . "\n";
        }
    }
}
