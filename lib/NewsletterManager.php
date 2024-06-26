<?php

namespace FriendsOfRedaxo\MultiNewsletter;

use rex;
use rex_addon;
use rex_article;
use rex_config;
use rex_mailer;
use rex_request;
use rex_sql;
use rex_user;
use rex_view;

/**
 * MultiNewsletter Newletter (noch zu versenden).
 * @api
 *
 * @author Tobias Krais
 */
class NewsletterManager
{
    /**
     * @var array<int,Newsletter> Archiv Objekte des Newsletters. ACHTUNG: der Index im Array muss die Archiv ID sein.
     */
    public array $archives = [];

    /** @var array<User> empfänger des Newsletters */
    public array $recipients = [];

    /**
     * @var bool true if only autosend archives and receipients should be managed
     */
    public bool $autosend_only = false;

    /** @var array<User> users an die der Newsletter zuletzt versand wurde */
    public array $last_send_users = [];

    /** @var int Anzahl ausstehender Newsletter Mails */
    public int $remaining_users = 0;

    /**
     * Stellt die Daten des Newsletters aus einem Archiv zusammen.
     * @param int $numberMails anzahl der Mails für den nächsten Versandschritt
     * @param bool $autosend_only init only reciepients with autosend option
     */
    public function __construct($numberMails = 0, $autosend_only = false)
    {
        $this->autosend_only = $autosend_only;
        $this->initArchivesToSend();
        $this->initRecipients($numberMails);
        $this->cleanupSendlistOrphans();
    }

    /**
     * Cleans up (deletes all recipients in archives that are older than 4 weeks.
     * Deletes also all recipients that did not activate their subscription within
     * last 4 weeks.
     */
    public static function autoCleanup(): void
    {
        // Cleanup archives
        $query = 'SELECT id FROM '. rex::getTablePrefix() .'375_archive '
            ."WHERE sentdate < '". date('Y-m-d H:i:s', strtotime('-4 weeks')) ."' "
                ."AND recipients NOT LIKE '%Addresses deleted.%'";
        $result = rex_sql::factory();
        $result->setQuery($query);

        for ($i = 0; $result->getRows() > $i; ++$i) {
            $newsletter = new Newsletter((int) $result->getValue('id'));
            $newsletter->recipients = [count($newsletter->recipients) .' recipients. Addresses deleted.'];
            $newsletter->recipients_failure = [count($newsletter->recipients_failure) .' recipients with send failure. Addresses deleted.'];
            $newsletter->save();
            echo rex_view::success("Newsletter '". $newsletter->subject ."' recipient addresses deleted.". PHP_EOL);

            $result->next();
        }

        // Cleanup not activated users
        $query = 'SELECT id FROM '. rex::getTablePrefix() .'375_user '
            ."WHERE (activationkey IS NOT NULL AND activationkey != '' AND activationkey != '0') AND createdate < '". date('Y-m-d H:i:s', strtotime('-4 weeks')) ."'";
        $result->setQuery($query);
        for ($i = 0; $result->getRows() > $i; ++$i) {
            $user = new User((int) $result->getValue('id'));
            $user->delete();
            echo rex_view::success($user->email .' deleted, because not activated for more than 4 weeks.'. PHP_EOL);

            $result->next();
        }
    }

    /**
     * Versendet einen Newsletter sofort.
     * @param int[] $group_ids array mit den GruppenIDs der vorzubereitenden Gruppen
     * @param int $article_id ID des zu versendenden Redaxo Artikels
     * @param int $fallback_clang_id ID der Sprache, die verwendet werden soll,
     * wenn der Artikel offline ist
     * @param int[] $recipient_ids IDs of receipients that should be added to send list
     * @param string $attachments Attachment list, comma separated
     * @return bool true if successful started, otherwise false
     */
    public static function autosend($group_ids, $article_id, $fallback_clang_id, array $recipient_ids = [], $attachments = '')
    {
        $newsletterManager = self::factory();
        $newsletterManager->autosend_only = true;
        $newsletterManager->prepare($group_ids, $article_id, $fallback_clang_id, $recipient_ids, $attachments);

        $cronjob_sender = CronjobSender::factory();
        if ($cronjob_sender->isInstalled() && count($newsletterManager->archives) > 0) {
            // Activate Cronjob
            $cronjob_sender->activate();
            return true;
        }

        // Send it all right now - or try it at least
        $newsletterManager->send(count($newsletterManager->recipients));
        // Send final admin notification
        foreach ($newsletterManager->archives as $archive) {
            if (0 === $archive->countRemainingUsers()) {
                $subject = 'Versand Newsletter abgeschlossen';
                $body = 'Der Versand das folgenden Newsletters wurde abgeschlossen:<br>'
                    . $archive->subject .'<br><br>'
                    . 'Der Versand per Cronjob war nicht möglich und wurde daher ohne Berücksichtigung möglicher Serverlimits auf ein mal durchgeführt. Bitte installieren Sie das Cronjob Addon und aktivieren Sie außerdem den MutliNewsletter Sender Cronjob über die Einstellungen des MultiNewsletters.<br><br>'
                    . 'Fehler gab es beim Versand an folgende Nutzer:<br>'
                    . implode(', ', $archive->recipients_failure);
                $newsletterManager->sendAdminNotification($subject, $body);
            }
        }

        return false;
    }

    /**
     * Deletes all receipientes from sendlist that where deleted after sendlist was set up.
     */
    private function cleanupSendlistOrphans(): void
    {
        $query = 'SELECT sendlist.user_id FROM '. rex::getTablePrefix() .'375_sendlist AS sendlist '
                .'LEFT JOIN '. rex::getTablePrefix() .'375_user AS users ON sendlist.user_id = users.id '
                .'WHERE users.id IS NULL;';
        $result = rex_sql::factory();
        $result->setQuery($query);
        for ($i = 0; $result->getRows() > $i; ++$i) {
            $query_delete = 'DELETE FROM '. rex::getTablePrefix() .'375_sendlist WHERE user_id = '. $result->getValue('user_id');
            $result_delete = rex_sql::factory();
            $result_delete->setQuery($query_delete);

            $result->next();
        }
    }

    /**
     * Sends next step of newletters in send list.
     */
    public static function cronSend(): void
    {
        // Calculate maximum mails per Cronjob step (every 5 minutes)
        $numberMails = (int) round(rex_config::get('multinewsletter', 'max_mails') * rex_config::get('multinewsletter', 'versandschritte_nacheinander') * 3600 / rex_config::get('multinewsletter', 'sekunden_pause') / 12);
        $newsletterManager = new self($numberMails, true);
        $newsletterManager->send($numberMails);

        // Send final admin notification
        foreach ($newsletterManager->archives as $archive) {
            if (0 === $archive->countRemainingUsers()) {
                $subject = 'Versand Newsletter abgeschlossen';
                $body = 'Der automatisierte Versand des folgenden Newsletters wurde abgeschlossen:<br>'
                    .'<b>'. $archive->subject .'</b>'
                    .'<br><br>Anzahl erfolgreich versendete Empfänger: '. count($archive->recipients);
                if (count($archive->recipients_failure) > 0) {
                    $body .= '<br><br>Fehler gab es beim Versand an folgende Nutzer:<br>- ';
                    foreach ($archive->recipients_failure as $recipient_email) {
                        if (false === filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
                            continue;
                        }

                        $recipient = User::initByMail($recipient_email);
                        $recipient_text = '';
                        if ($recipient instanceof User && '' !== $recipient->firstname) {
                            $recipient_text = $recipient->firstname .' ';
                        }
                        if ($recipient instanceof User && '' !== $recipient->lastname) {
                            $recipient_text = $recipient->lastname .' ';
                        }
                        $recipient_text .= '<'. $recipient_email .'><br>';
                        $body .= $recipient_text;
                    }
                }
                $body .= '<br><br>Details finden Sie in den Archiven des MultiNewsletters und im Cronjob Log.';
                $newsletterManager->sendAdminNotification($subject, $body);
                // Unset archive
                unset($newsletterManager->archives[$archive->id]);
            }
        }

        // Deactivate Cronjob
        if (0 === count($newsletterManager->archives)) {
            CronjobSender::factory()->deactivate();
        }

        echo rex_view::success('Step completed.');
    }

    /**
     * Get newsletter archives which are on send list.
     * @param bool $manual_send_only if true, autosend archives are excluded
     * @return array<int,Newsletter> Array with Newsletter archives
     */
    public static function getArchivesToSend($manual_send_only = true)
    {
        $query = 'SELECT archive_id FROM '. rex::getTablePrefix() .'375_sendlist '
            .($manual_send_only ? 'WHERE autosend = 0 ' : '')
            .'GROUP BY archive_id';
        $result = rex_sql::factory();
        $result->setQuery($query);

        $newsletter_archives = [];
        for ($i = 0; $result->getRows() > $i; ++$i) {
            $newsletter = new Newsletter((int) $result->getValue('archive_id'));
            $newsletter_archives[$newsletter->id] = $newsletter;

            $result->next();
        }

        return $newsletter_archives;
    }

    /**
     * Creates a blank, uninitialized self object.
     * @return self empty self object
     */
    public static function factory()
    {
        $manager = new self();
        $manager->archives = [];
        $manager->recipients = [];
        return $manager;
    }

    /**
     * Initialisiert die Newsletter Archive, die zum Versand ausstehen.
     */
    private function initArchivesToSend(): void
    {
        $query = 'SELECT archive_id FROM '. rex::getTablePrefix() .'375_sendlist '
            .($this->autosend_only ? 'WHERE autosend = 1 ' : '')
            .'GROUP BY archive_id';
        $result = rex_sql::factory();
        $result->setQuery($query);
        $num_rows = $result->getRows();

        for ($i = 0; $num_rows > $i; ++$i) {
            $archive_id = (int) $result->getValue('archive_id');
            $this->archives[$archive_id] = new Newsletter($archive_id);
            $result->next();
        }
    }

    /**
     * Initialisiert die Newsletter Empfänger, die zum Versand ausstehen.
     * @param int $numberMails anzahl der Mails für den nächsten Versandschritt
     */
    private function initRecipients($numberMails = 0): void
    {
        $query = 'SELECT id FROM ' . rex::getTablePrefix() . '375_sendlist AS sendlist '
            . 'LEFT JOIN ' . rex::getTablePrefix() . '375_user AS users '
                . 'ON sendlist.user_id = users.id '
            . 'WHERE id > 0 '
            .($this->autosend_only ? 'AND autosend = 1 ' : '')
            . 'ORDER BY archive_id, email';
        if ($numberMails > 0) {
            $query .= ' LIMIT 0, ' . $numberMails;
        }
        $result = rex_sql::factory();
        $result->setQuery($query);
        $num_rows = $result->getRows();
        for ($i = 0; $num_rows > $i; ++$i) {
            $this->recipients[] = new User((int) $result->getValue('id'));
            $result->next();
        }
    }

    /**
     * Returns pending user number. If autosend_only in this object is true,
     * only autosend number is returned, otherwise only not autosend user number
     * is returned.
     * @return int Pending user number
     */
    public function countRemainingUsers()
    {
        if (0 === $this->remaining_users) {
            $query = 'SELECT COUNT(*) as total FROM ' . rex::getTablePrefix() . '375_sendlist'
                .' WHERE autosend = '. ($this->autosend_only ? '1' : '0');
            $result = rex_sql::factory();
            $result->setQuery($query);

            return (int) $result->getValue('total');
        }

        return $this->remaining_users;

    }

    /**
     * Bereitet den Versand des Newsletters vor.
     * @param int[] $group_ids array mit den GruppenIDs der vorzubereitenden Gruppen
     * @param int $article_id ID des zu versendenden Redaxo Artikels
     * @param int $fallback_clang_id ID der Sprache, die verwendet werden soll,
     * wenn der Artikel offline ist
     * @param int[] $recipient_ids IDs of receipients that should be added to send list
     * @param string $attachments Attachment list, comma separated
     * @return int[] array mit den Sprach IDs, die Offline sind und durch die
     * Fallback Sprache ersetzt wurden
     */
    public function prepare($group_ids, $article_id, $fallback_clang_id, array $recipient_ids = [], $attachments = '')
    {
        $offline_lang_ids = [];

        $clang_ids = [];
        // Welche Sprachen sprechen die Nutzer der vorzubereitenden Gruppen?
        $where_groups = [];
        foreach ($group_ids as $group_id) {
            $where_groups[] = 'FIND_IN_SET('. $group_id .', REPLACE(group_ids, "|", ","))';
        }
        if (count($recipient_ids) > 0) {
            $where_groups[] = 'id IN(' . implode(',', $recipient_ids) . ')';
        }
        $query = 'SELECT clang_id FROM ' . rex::getTablePrefix() . '375_user '
            . 'WHERE ' . implode(' OR ', $where_groups) . ' GROUP BY clang_id';

        $result = rex_sql::factory();
        $result->setQuery($query);
        $num_rows = $result->getRows();
        for ($i = 0; $num_rows > $i; ++$i) {
            $clang_ids[] = (int) $result->getValue('clang_id');
            $result->next();
        }

        // Read article
        $new_archives = [];
        foreach ($clang_ids as $clang_id) {
            $newsletter = Newsletter::factory($article_id, $clang_id);

            if (0 === strlen($newsletter->htmlbody)) {
                $offline_lang_ids[] = $clang_id;
            } else {
                if('' !== $attachments) {
                    $newsletter->attachments = explode(',', $attachments);
                }
                $newsletter->group_ids = $group_ids;
                $newsletter->sender_email = (string) rex_config::get('multinewsletter', 'sender');
                $newsletter->sender_name = (string) rex_config::get('multinewsletter', 'lang_1_sendername');
                $newsletter->reply_to_email = (string) rex_config::get('multinewsletter', 'reply_to');
                $session = rex_request::session('multinewsletter');
                if(PHP_SESSION_NONE !== session_status() && is_array($session) && isset($session['newsletter']) && is_array($session['newsletter'])) {
                    if (isset($session['newsletter']['sender_email'])) {
                        $newsletter->sender_email = (string) $session['newsletter']['sender_email'];
                    }
                    if (isset($session['newsletter']['sender_name']) && isset($session['newsletter']['sender_name'][$clang_id])) {
                        $newsletter->sender_name = $session['newsletter']['sender_name'][$clang_id];
                    }
                    if (isset($session['newsletter']['reply_to_email'])) {
                        $newsletter->reply_to_email = $session['newsletter']['reply_to_email'];
                    }
                }
                $newsletter->sentby = rex::getUser() instanceof rex_user ? rex::getUser()->getLogin() : 'MultiNewsletter Cronjob API Call';
                $newsletter->save();

                $new_archives[$newsletter->id] = $newsletter;
                $this->archives[$newsletter->id] = $newsletter;
            }
        }

        // Add users to send list
        $where_offline_langs = [];
        foreach ($offline_lang_ids as $offline_lang_id) {
            $where_offline_langs[] = 'clang_id = ' . $offline_lang_id;
        }
        foreach ($new_archives as $archive_id => $newsletter) {
            $newsletter_lang_id = $newsletter->clang_id;

            if (!in_array($newsletter_lang_id, $offline_lang_ids, true)) {
                $query_add_users = 'INSERT INTO `' . rex::getTablePrefix() . '375_sendlist` (`archive_id`, `user_id`, `autosend`) '
                    . 'SELECT '. $archive_id .' AS archive_id, `id`, '. ($this->autosend_only ? 1 : 0) .' AS autosend '
                        . 'FROM `' . rex::getTablePrefix() . '375_user` WHERE (' . implode(' OR ', $where_groups) . ') AND (clang_id = '. $newsletter_lang_id;
                if ($newsletter_lang_id === $fallback_clang_id && count($where_offline_langs) > 0) {
                    $query_add_users .= ' OR ' . implode(' OR ', $where_offline_langs);
                }
                $query_add_users .= ") AND `status` = 1 AND email != ''";
                $result_add_users = rex_sql::factory();
                $result_add_users->setQuery($query_add_users);
            }
        }

        return $offline_lang_ids;
    }

    /**
     * Reset newsletter sendlist. If no archive ID is submitted, the complete
     * sendlist is deleted. Additionally, alls unsent archives are deleted from
     * archive table.
     * @param int $archive_id archive id to delete
     */
    public function reset($archive_id = 0): void
    {
        // Reset users
        $query_user = 'TRUNCATE ' . rex::getTablePrefix() . '375_sendlist';
        if ($archive_id > 0) {
            $query_user = 'DELETE FROM ' . rex::getTablePrefix() . '375_sendlist WHERE archive_id = '. $archive_id;
        }
        $result_user = rex_sql::factory();
        $result_user->setQuery($query_user);
        $this->recipients = [];

        // Delete unsent archive(s)
        $query_archive = 'DELETE FROM ' . rex::getTablePrefix() . '375_archive WHERE sentdate = 0'. ($archive_id > 0 ? ' AND id = '. $archive_id : '');
        $result_archive = rex_sql::factory();
        $result_archive->setQuery($query_archive);
        $this->archives = [];

        $this->remaining_users = 0;
    }

    /**
     * Veranlasst das Senden der nächsten Trange von Mails.
     * @param int $numberMails Number of mails to send
     * @return bool|array<string> true, when sent successfully, otherwise array with failed email addresses
     */
    public function send($numberMails)
    {
        if ($numberMails > count($this->recipients)) {
            $numberMails = count($this->recipients);
        }

        $result = rex_sql::factory();
        $failure_mails = [];
        $success_mails = [];

        while ($numberMails > 0) {
            $recipient = $this->recipients[$numberMails - 1];
            $archive_id = $recipient->getSendlistArchiveIDs($this->autosend_only);
            $newsletter = $this->archives[$archive_id[0]];

            if (false === $newsletter->sendNewsletter($recipient, rex_article::get($newsletter->article_id))) {
                $result->setQuery('DELETE FROM '. rex::getTablePrefix() .'375_sendlist WHERE user_id = '. $recipient->id .' AND archive_id = '. $newsletter->id);
                $failure_mails[] = $recipient->email;
            }

            // Delete user from sendlist
            $result->setQuery('DELETE FROM '. rex::getTablePrefix() .'375_sendlist WHERE user_id = '. $recipient->id .' AND archive_id = '. $newsletter->id);

            $this->last_send_users[] = $recipient;
            $success_mails[] = $recipient->email;
            --$numberMails;
        }

        if (0 === count($failure_mails)) {
            return true;
        }

        return $failure_mails;

    }

    /**
     * Sends a email to MutliNewsletter admin, if given in settings.
     * @param string $subject Message subject
     * @param string $body Message body
     * @return bool true if successfully sent, otherwise false
     */
    private function sendAdminNotification($subject, $body)
    {
        $multinewsletter = rex_addon::get('multinewsletter');
        if ('' !== $multinewsletter->getConfig('admin_email', '')) {
            $multinewsletter = rex_addon::get('multinewsletter');

            $mail = new rex_mailer();
            $mail->isHTML(true);
            $mail->CharSet = 'utf-8';
            $mail->From = (string) $multinewsletter->getConfig('sender');
            $mail->FromName = 'MultiNewsletter Manager';
            $mail->Sender = (string) $multinewsletter->getConfig('sender');
            $mail->addAddress((string) $multinewsletter->getConfig('admin_email'));

            if (1 === (int) $multinewsletter->getConfig('use_smtp')) {
                $mail->Mailer = 'smtp';
                $mail->Host = (string) $multinewsletter->getConfig('smtp_host');
                $mail->Port = (int) $multinewsletter->getConfig('smtp_port');
                $mail->SMTPSecure = (string) $multinewsletter->getConfig('smtp_crypt');
                $mail->SMTPAuth =(bool) $multinewsletter->getConfig('smtp_auth');
                $mail->Username = (string) $multinewsletter->getConfig('smtp_user');
                $mail->Password = (string) $multinewsletter->getConfig('smtp_password');
            }

            $mail->Subject = $subject;
            $mail->Body = $body;
            $success = $mail->send();
            if (!$success) {
                echo rex_view::error('Error sending admin notification: '. $mail->ErrorInfo);
            }
            return $success;
        }

        return false;
    }
}
