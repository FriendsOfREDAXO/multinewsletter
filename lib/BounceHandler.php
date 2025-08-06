<?php

namespace FriendsOfRedaxo\MultiNewsletter;

use rex;
use rex_addon;
use rex_config;
use rex_extension;
use rex_extension_point;
use rex_sql;
use rex_view;

/**
 * IMAP-based Bounce Handler for MultiNewsletter
 * Processes bounced emails to automatically manage user subscriptions
 * 
 * @author MultiNewsletter Team
 */
class BounceHandler
{
    /** @var resource IMAP connection resource */
    private $imap_connection;
    
    /** @var string IMAP server host */
    private string $imap_host = '';
    
    /** @var int IMAP server port */
    private int $imap_port = 993;
    
    /** @var string IMAP username */
    private string $imap_user = '';
    
    /** @var string IMAP password */
    private string $imap_password = '';
    
    /** @var bool Use SSL/TLS */
    private bool $imap_ssl = true;
    
    /** @var string IMAP mailbox name */
    private string $imap_mailbox = 'INBOX';
    
    /** @var array<string> Processed message UIDs to avoid duplicates */
    private array $processed_uids = [];

    /**
     * Initialize BounceHandler with IMAP configuration
     */
    public function __construct()
    {
        $addon = rex_addon::get('multinewsletter');
        $this->imap_host = (string) $addon->getConfig('bounce_imap_host', '');
        $this->imap_port = (int) $addon->getConfig('bounce_imap_port', 993);
        $this->imap_user = (string) $addon->getConfig('bounce_imap_user', '');
        $this->imap_password = (string) $addon->getConfig('bounce_imap_password', '');
        $this->imap_ssl = (bool) $addon->getConfig('bounce_imap_ssl', true);
        $this->imap_mailbox = (string) $addon->getConfig('bounce_imap_mailbox', 'INBOX');
        
        $this->loadProcessedUIDs();
    }

    /**
     * Connect to IMAP server
     * @return bool true if connection successful
     */
    public function connect(): bool
    {
        if (!extension_loaded('imap')) {
            throw new \Exception('PHP IMAP extension is not installed');
        }

        $mailbox = '{' . $this->imap_host . ':' . $this->imap_port;
        if ($this->imap_ssl) {
            $mailbox .= '/imap/ssl/novalidate-cert';
        }
        $mailbox .= '}' . $this->imap_mailbox;

        $this->imap_connection = @imap_open($mailbox, $this->imap_user, $this->imap_password);
        
        if (false === $this->imap_connection) {
            throw new \Exception('IMAP connection failed: ' . imap_last_error());
        }

        return true;
    }

    /**
     * Disconnect from IMAP server
     */
    public function disconnect(): void
    {
        if (is_resource($this->imap_connection)) {
            imap_close($this->imap_connection);
        }
    }

    /**
     * Process bounced emails from IMAP inbox
     * @return array Array with processing results
     */
    public function processBounces(): array
    {
        $results = [
            'processed' => 0,
            'hard_bounces' => 0,
            'soft_bounces' => 0,
            'spam_complaints' => 0,
            'errors' => []
        ];

        if (!$this->connect()) {
            $results['errors'][] = 'Failed to connect to IMAP server';
            return $results;
        }

        try {
            $emails = imap_search($this->imap_connection, 'UNSEEN');
            
            if (false === $emails) {
                return $results; // No new emails
            }

            foreach ($emails as $email_number) {
                $uid = imap_uid($this->imap_connection, $email_number);
                
                // Skip if already processed
                if (in_array($uid, $this->processed_uids, true)) {
                    continue;
                }

                $bounce_result = $this->processBounceEmail($email_number);
                
                if ($bounce_result) {
                    $results['processed']++;
                    $results[$bounce_result['type']]++;
                    
                    // Mark as processed
                    $this->processed_uids[] = $uid;
                    
                    // Mark email as seen
                    imap_setflag_full($this->imap_connection, $uid, '\\Seen', ST_UID);
                }
            }
            
            $this->saveProcessedUIDs();
            
        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
        } finally {
            $this->disconnect();
        }

        return $results;
    }

    /**
     * Process individual bounced email
     * @param int $email_number Email number in mailbox
     * @return array|null Bounce information or null if not a bounce
     */
    private function processBounceEmail(int $email_number): ?array
    {
        $header = imap_headerinfo($this->imap_connection, $email_number);
        $body = imap_body($this->imap_connection, $email_number);
        $structure = imap_fetchstructure($this->imap_connection, $email_number);

        // Extract original recipient email from bounce
        $bounced_email = $this->extractBouncedEmail($header, $body, $structure);
        
        if (!$bounced_email) {
            return null; // Not a valid bounce
        }

        // Determine bounce type
        $bounce_type = $this->determineBounceType($header, $body);
        
        // Process bounce based on type
        $this->handleBounce($bounced_email, $bounce_type, $header, $body);
        
        return [
            'email' => $bounced_email,
            'type' => $bounce_type,
            'subject' => $header->subject ?? '',
            'date' => date('Y-m-d H:i:s', $header->udate ?? time())
        ];
    }

    /**
     * Extract bounced email address from bounce message
     * @param object $header Email header
     * @param string $body Email body
     * @param object $structure Email structure
     * @return string|null Bounced email address
     */
    private function extractBouncedEmail(object $header, string $body, object $structure): ?string
    {
        // Common patterns for extracting email from bounce messages
        $patterns = [
            '/(?:Final-Recipient:|final-recipient:)\s*(?:rfc822;)?\s*([^\s]+@[^\s]+)/i',
            '/(?:Original-Recipient:|original-recipient:)\s*(?:rfc822;)?\s*([^\s]+@[^\s]+)/i',
            '/(?:User-Agent:|Return-Path:)\s*<([^>]+@[^>]+)>/i',
            '/(?:failed|bounced|undeliverable).*?(?:to|for)\s+([^\s]+@[^\s]+)/i',
            '/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i' // Generic email pattern
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $body, $matches)) {
                $email = filter_var($matches[1], FILTER_VALIDATE_EMAIL);
                if ($email && $this->isNewsletterRecipient($email)) {
                    return $email;
                }
            }
        }

        // Try to extract from headers
        if (isset($header->reply_to[0])) {
            $email = $header->reply_to[0]->mailbox . '@' . $header->reply_to[0]->host;
            if (filter_var($email, FILTER_VALIDATE_EMAIL) && $this->isNewsletterRecipient($email)) {
                return $email;
            }
        }

        return null;
    }

    /**
     * Determine bounce type (hard, soft, spam complaint)
     * @param object $header Email header
     * @param string $body Email body
     * @return string Bounce type
     */
    private function determineBounceType(object $header, string $body): string
    {
        $body_lower = strtolower($body);
        $subject_lower = strtolower($header->subject ?? '');

        // Hard bounce indicators
        $hard_bounce_indicators = [
            'user unknown', 'mailbox unavailable', 'invalid recipient',
            'no such user', 'user not found', 'recipient rejected',
            'mailbox does not exist', 'invalid address', 'user disabled',
            '5.1.1', '5.1.2', '5.1.3', '5.2.1', '5.7.1'
        ];

        // Soft bounce indicators  
        $soft_bounce_indicators = [
            'mailbox full', 'quota exceeded', 'temporary failure',
            'try again later', 'mailbox temporarily unavailable',
            'server busy', 'message deferred',
            '4.2.2', '4.3.1', '4.3.2', '4.4.7'
        ];

        // Spam complaint indicators
        $spam_indicators = [
            'spam', 'junk', 'abuse', 'complaint', 'unsubscribe',
            'feedback loop', 'fbl', 'x-hmxmroriginalrecipient'
        ];

        // Check for spam complaints first
        foreach ($spam_indicators as $indicator) {
            if (str_contains($body_lower, $indicator) || str_contains($subject_lower, $indicator)) {
                return 'spam_complaints';
            }
        }

        // Check for hard bounces
        foreach ($hard_bounce_indicators as $indicator) {
            if (str_contains($body_lower, $indicator) || str_contains($subject_lower, $indicator)) {
                return 'hard_bounces';
            }
        }

        // Check for soft bounces
        foreach ($soft_bounce_indicators as $indicator) {
            if (str_contains($body_lower, $indicator) || str_contains($subject_lower, $indicator)) {
                return 'soft_bounces';
            }
        }

        // Default to soft bounce if uncertain
        return 'soft_bounces';
    }

    /**
     * Handle bounce based on type
     * @param string $email Bounced email address
     * @param string $bounce_type Type of bounce
     * @param object $header Email header
     * @param string $body Email body
     */
    private function handleBounce(string $email, string $bounce_type, object $header, string $body): void
    {
        $user = User::initByMail($email);
        
        if (!($user instanceof User)) {
            return; // User not found
        }

        // Log bounce
        $this->logBounce($user->id, $bounce_type, $header->subject ?? '', $body);

        switch ($bounce_type) {
            case 'hard_bounces':
                $this->handleHardBounce($user);
                break;
                
            case 'soft_bounces':
                $this->handleSoftBounce($user);
                break;
                
            case 'spam_complaints':
                $this->handleSpamComplaint($user);
                break;
        }

        // Extension point for custom bounce handling
        rex_extension::registerPoint(new rex_extension_point('MULTINEWSLETTER_BOUNCE_PROCESSED', [
            'user' => $user,
            'bounce_type' => $bounce_type,
            'email' => $email
        ]));
    }

    /**
     * Handle hard bounce - immediately deactivate user
     * @param User $user Newsletter user
     */
    private function handleHardBounce(User $user): void
    {
        $user->status = 0; // Deactivate
        $user->save();
        
        echo rex_view::info("Hard bounce: User {$user->email} deactivated");
    }

    /**
     * Handle soft bounce - increment counter, deactivate after threshold
     * @param User $user Newsletter user
     */
    private function handleSoftBounce(User $user): void
    {
        $soft_bounce_count = $this->getSoftBounceCount($user->id);
        $soft_bounce_count++;
        
        $this->updateSoftBounceCount($user->id, $soft_bounce_count);
        
        // Deactivate after 3 soft bounces
        if ($soft_bounce_count >= 3) {
            $user->status = 0;
            $user->save();
            echo rex_view::warning("Soft bounce threshold reached: User {$user->email} deactivated");
        } else {
            echo rex_view::info("Soft bounce #{$soft_bounce_count}: {$user->email}");
        }
    }

    /**
     * Handle spam complaint - immediately unsubscribe
     * @param User $user Newsletter user
     */
    private function handleSpamComplaint(User $user): void
    {
        $user->unsubscribe('delete');
        
        echo rex_view::warning("Spam complaint: User {$user->email} unsubscribed");
    }

    /**
     * Check if email is a newsletter recipient
     * @param string $email Email address to check
     * @return bool true if email is newsletter recipient
     */
    private function isNewsletterRecipient(string $email): bool
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . '375_user WHERE email = ?', [$email]);
        
        return $sql->getRows() > 0;
    }

    /**
     * Log bounce to database
     * @param int $user_id User ID
     * @param string $bounce_type Bounce type
     * @param string $subject Email subject
     * @param string $body Email body
     */
    private function logBounce(int $user_id, string $bounce_type, string $subject, string $body): void
    {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . '375_bounces');
        $sql->setValue('user_id', $user_id);
        $sql->setValue('bounce_type', $bounce_type);
        $sql->setValue('subject', $subject);
        $sql->setValue('body_excerpt', substr($body, 0, 1000));
        $sql->setValue('created_at', date('Y-m-d H:i:s'));
        $sql->insert();
    }

    /**
     * Get soft bounce count for user
     * @param int $user_id User ID
     * @return int Soft bounce count
     */
    private function getSoftBounceCount(int $user_id): int
    {
        $sql = rex_sql::factory();
        $sql->setQuery(
            'SELECT soft_bounce_count FROM ' . rex::getTablePrefix() . '375_user WHERE id = ?',
            [$user_id]
        );
        
        return (int) $sql->getValue('soft_bounce_count');
    }

    /**
     * Update soft bounce count for user
     * @param int $user_id User ID
     * @param int $count New bounce count
     */
    private function updateSoftBounceCount(int $user_id, int $count): void
    {
        $sql = rex_sql::factory();
        $sql->setQuery(
            'UPDATE ' . rex::getTablePrefix() . '375_user SET soft_bounce_count = ? WHERE id = ?',
            [$count, $user_id]
        );
    }

    /**
     * Load processed message UIDs from database
     */
    private function loadProcessedUIDs(): void
    {
        $addon = rex_addon::get('multinewsletter');
        $uids_string = (string) $addon->getConfig('bounce_processed_uids', '');
        
        if ('' !== $uids_string) {
            $this->processed_uids = explode(',', $uids_string);
        }
    }

    /**
     * Save processed message UIDs to database
     */
    private function saveProcessedUIDs(): void
    {
        // Keep only last 1000 UIDs to prevent unlimited growth
        $this->processed_uids = array_slice($this->processed_uids, -1000);
        
        $addon = rex_addon::get('multinewsletter');
        rex_config::set('multinewsletter', 'bounce_processed_uids', implode(',', $this->processed_uids));
    }

    /**
     * Test IMAP connection
     * @return bool true if connection successful
     */
    public function testConnection(): bool
    {
        try {
            $this->connect();
            $this->disconnect();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get bounce statistics
     * @param int $days Number of days to look back
     * @return array Bounce statistics
     */
    public function getBounceStatistics(int $days = 30): array
    {
        $sql = rex_sql::factory();
        $sql->setQuery(
            'SELECT bounce_type, COUNT(*) as count 
             FROM ' . rex::getTablePrefix() . '375_bounces 
             WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY) 
             GROUP BY bounce_type',
            [$days]
        );

        $stats = [
            'hard_bounces' => 0,
            'soft_bounces' => 0, 
            'spam_complaints' => 0,
            'total' => 0
        ];

        for ($i = 0; $i < $sql->getRows(); $i++) {
            $type = $sql->getValue('bounce_type');
            $count = (int) $sql->getValue('count');
            $stats[$type] = $count;
            $stats['total'] += $count;
            $sql->next();
        }

        return $stats;
    }
}
