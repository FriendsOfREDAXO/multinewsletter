<?php

namespace FriendsOfRedaxo\MultiNewsletter;

use rex;
use rex_addon;
use rex_article;
use rex_article_content;
use rex_clang;
use rex_config;
use rex_extension;
use rex_extension_point;
use rex_i18n;
use rex_mailer;
use rex_media;
use rex_path;
use rex_socket;
use rex_socket_exception;
use rex_socket_response;
use rex_sql;
use rex_view;
use rex_yrewrite;

/**
 * MultiNewsletter Newsletter (stored in database table rex_375_archive).
 * @api
 *
 * @author Tobias Krais
 */
class Newsletter
{
    /** @var int Database ID */
    public int $id = 0;

    /** @var int Redaxo article id */
    public int $article_id = 0;

    /** @var int Redaxo language id */
    public int $clang_id = 0;

    /** @var string Subject */
    public string $subject = '';

    /** @var string Body */
    public string $htmlbody = '';

    /** @var array<string> Array with attachment file names */
    public array $attachments = [];

    /** @var array<string> Array with recipient email addresses */
    public array $recipients = [];

    /** @var array<string> Array with recipient email addresses that failed to send */
    public array $recipients_failure = [];

    /** @var array<int> Array with group ids */
    public array $group_ids = [];

    /** @var string Sender email address */
    public string $sender_email = '';

    /** @var string Sender name */
    public string $sender_name = '';

    /** @var string Reply to email address */
    public string $reply_to_email = '';

    /** @var string Setup date (format: Y-m-d H:i:s) */
    public string $setupdate = '';

    /** @var string Send date (format: Y-m-d H:i:s) */
    public string $sentdate = '';

    /** @var string Redaxo send user name */
    public string $sentby = '';

    /** @var int Number of remaining users in sendlist */
    public int $remaining_users = 0;

    /**
     * Gets object data from database.
     * @param int $id Archive ID
     */
    public function __construct($id)
    {
        $query = 'SELECT * FROM '. \rex::getTablePrefix() .'375_archive WHERE id = '. $id;
        $result = \rex_sql::factory();
        $result->setQuery($query);

        if ($result->getRows() > 0) {
            $this->id = (int) $result->getValue('id');
            $this->article_id = (int) $result->getValue('article_id');
            $this->clang_id = (int) $result->getValue('clang_id');
            $this->subject = stripslashes(htmlspecialchars_decode((string) $result->getValue('subject')));
            $this->htmlbody = (string) base64_decode((string) $result->getValue('htmlbody'), true);
            $attachment_separator = str_contains((string) $result->getValue('attachments'), '|') ? '|' : ',';
            $attachments = preg_grep('/^\s*$/s', explode($attachment_separator, (string) $result->getValue('attachments')), PREG_GREP_INVERT);
            $this->attachments = is_array($attachments) ? $attachments : [];
            $recipients_separator = str_contains((string) $result->getValue('recipients'), '|') ? '|' : ',';
            $recipients = preg_grep('/^\s*$/s', explode($recipients_separator, (string) $result->getValue('recipients')), PREG_GREP_INVERT);
            $this->recipients = is_array($recipients) ? $recipients : [];
            $recipients_failure = preg_grep('/^\s*$/s', explode(',', (string) $result->getValue('recipients_failure')), PREG_GREP_INVERT);
            $this->recipients_failure = is_array($recipients_failure) ? $recipients_failure : [];
            $group_ids = preg_grep('/^\s*$/s', explode('|', (string) $result->getValue('group_ids')), PREG_GREP_INVERT);
            $this->group_ids = is_array($group_ids) ? array_map('intval', $group_ids) : [];
            $this->sender_email = (string) $result->getValue('sender_email');
            $this->sender_name = stripslashes((string) $result->getValue('sender_name'));
            $this->reply_to_email = (string) $result->getValue('reply_to_email');
            $this->setupdate = (string) $result->getValue('setupdate');
            $this->sentdate = (string) $result->getValue('sentdate');
            $this->sentby = (string) $result->getValue('sentby');
        }
    }

    /**
     * Counts remaining users in sendlist.
     * @return int Number of remaining unsers
     */
    public function countRemainingUsers()
    {
        if (0 === $this->remaining_users) {
            $query = 'SELECT COUNT(*) as total FROM ' . rex::getTablePrefix() . '375_sendlist '
                .'WHERE archive_id = '. $this->id;
            $result = rex_sql::factory();
            $result->setQuery($query);

            return (int) $result->getValue('total');
        }

        return $this->remaining_users;

    }

    /**
     * Creates a new newsletter archive.
     * @param int $article_id Redaxo article id
     * @param int $clang_id Redaxo clang id
     * @return self initialized MultiNewsletter object
     */
    public static function factory($article_id, $clang_id = 0)
    {
        if (0 === $clang_id) {
            $clang_id = \rex_clang::getCurrentId();
        }

        // init Mailbody and subject
        $newsletter = new self(0);
        $newsletter->readArticle($article_id, $clang_id);

        return $newsletter;
    }

    /**
     * Deletes archive.
     */
    public function delete(): void
    {
        $sql = rex_sql::factory();
        $sql->setQuery('DELETE FROM '. \rex::getTablePrefix() .'375_archive WHERE id = '. $this->id);
    }

    /**
     * Personalizes a string.
     * @param string $content Content that has to be personalized
     * @param User $user Recipient user object
     * @param rex_article|null $article Redaxo article
     * @return string Personalized string
     */
    public static function personalize($content, $user, $article = null)
    {
        return (string) preg_replace('/ {2,}/', ' ', self::replaceVars($content, $user, $article));
    }

    /**
     * Get article full URL, including domain.
     * @param int $article_id Redaxo article id
     * @param int $clang_id Redaxo clang id
     * @param string[] $params URL parameters
     * @return string
     */
    public static function getUrl($article_id = null, $clang_id = null, array $params = [])
    {
        $url = '';
        if (\rex_addon::get('yrewrite')->isAvailable()) {
            $url = rex_getUrl($article_id, $clang_id, $params);
        } else {
            $url = rtrim(rex::getServer(), '/') . '/' . ltrim(str_replace(['../', './'], '', rex_getUrl($article_id, $clang_id, $params)), '/');
        }
        return $url;
    }

    /**
     * Corrects URLs in content string.
     * @param string $content Content
     * @param int $article_id Redaxo article id
     * @param int $clang_id Redaxo language id
     * @return string String with corrected URLs
     */
    public static function replaceURLs($content, $article_id = 0, $clang_id = 0)
    {
        $current_domain = $article_id > 0 && \rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getDomainByArticleId($article_id, $clang_id)->getUrl() : (\rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer());
        $content = str_replace('href="/', 'href="'. $current_domain, $content);
        $content = str_replace('href="./', 'href="'. $current_domain, $content);
        $content = str_replace('href="../', 'href="'. $current_domain, $content);

        $content = str_replace("href='/", "href='". $current_domain, $content);
        $content = str_replace("href='./", "href='". $current_domain, $content);
        $content = str_replace("href='../", "href='". $current_domain, $content);

        $content = str_replace('src="/', 'src="'. $current_domain, $content);
        $content = str_replace('src="./', 'src="'. $current_domain, $content);
        $content = str_replace('src="../', 'src="'. $current_domain, $content);

        $content = str_replace("src='/", "src='". $current_domain, $content);
        $content = str_replace("src='./", "src='". $current_domain, $content);
        $content = str_replace("src='../", "src='". $current_domain, $content);

        $content = str_replace("src='index.php", "src='". $current_domain .'index.php', $content);
        $content = str_replace('src="index.php', 'src="'. $current_domain .'index.php', $content);

        // Correct image URLs
        $content = str_replace('&amp;', '&', $content);

        return $content;
    }

    /**
     * Personalized string.
     * @param string $content Content
     * @param User $user Recipient user object
     * @param rex_article $article Redaxo article
     * @return string Personalized content
     */
    public static function replaceVars($content, $user, $article = null)
    {
        $addon = rex_addon::get('multinewsletter');
        $clang_id = $user->clang_id > 0 ? $user->clang_id : rex_clang::getCurrentId();

        $replaces = [
            '+++GRAD+++' => $user->grad,
            '+++FIRSTNAME+++' => $user->firstname,
            '+++LASTNAME+++' => $user->lastname,
            '+++EMAIL+++' => $user->email,
        ];

        return strtr($content, rex_extension::registerPoint(
            new rex_extension_point(
                'multinewsletter.replaceVars', array_merge(
                    $replaces, [
                        '+++TITLE+++' => -1 === $user->title ? '' : $addon->getConfig('lang_' . $clang_id . '_title_' . $user->title),
                        '+++ABMELDELINK+++' => self::getUrl((int) $addon->getConfig('link_abmeldung'), $clang_id, ['unsubscribe' => $user->email]),
                        '+++AKTIVIERUNGSLINK+++' => self::getUrl((int) $addon->getConfig('link'), $clang_id, ['activationkey' => $user->activationkey, 'email' => $user->email]),
                        '+++NEWSLETTERLINK+++' => $article instanceof rex_article ? self::getUrl($article->getId(), $clang_id) .'?replace_vars=true&email='. $user->email : '',
                        '+++LINK_PRIVACY_POLICY+++' => rex_getUrl((int) rex_config::get('d2u_helper', 'article_id_privacy_policy', rex_article::getSiteStartArticleId()), $clang_id),
                        '+++LINK_IMPRESS+++' => rex_getUrl((int) rex_config::get('d2u_helper', 'article_id_impress', rex_article::getSiteStartArticleId()), $clang_id),
                    ]
                )
            )
        ));
    }

    /**
     * Get fallback lang settings.
     * @param int|null $fallback_lang
     * @return int|null rex_clang fallback clang_id
     */
    public static function getFallbackLang($fallback_lang = null)
    {
        $addon = rex_addon::get('multinewsletter');

        if (0 === (int) $addon->getConfig('lang_fallback', 0) && null !== $fallback_lang) {
            return $fallback_lang;
        }

        if (0 === (int) $addon->getConfig('lang_fallback', 0)) {
            return null;
        }

        return (int) rex_config::get('d2u_helper', 'default_lang', $fallback_lang);
    }

    /**
     * Reads a redaxo article in this object. First, it tries to read article
     * via HTTP request to be able to make use of all extension points and addons
     * like bloecks. If HTTP Request failes, article is read via Redaxo method.
     * @param int $article_id Redaxo article id
     * @param int $clang_id Redaxo clang id
     */
    private function readArticle($article_id, $clang_id): void
    {
        $article = rex_article::get($article_id, $clang_id);
        $article_content = new rex_article_content($article_id, $clang_id);

        if ($article instanceof rex_article && $article->isOnline()) {
            $this->article_id = $article_id;
            $this->clang_id = $clang_id;
            if ('socket' === (string) rex_config::get('multinewsletter', 'method', 'redaxo')) {
                $article_url = rtrim(rex::getServer(), '/') . '/' . ltrim(str_replace(['../', './'], '', rex_getUrl($article_id, $clang_id, ['replace_vars' => 1])), '/');
                if (rex_addon::get('yrewrite')->isAvailable()) {
                    $article_url = rex_yrewrite::getFullUrlByArticleId($article_id, $clang_id, ['replace_vars' => 1]);
                }
                $article_socket_response = null;
                try {
                    $article_socket_response = rex_socket::factoryUrl($article_url)->doGet();
                } catch (rex_socket_exception $e) {
                    // failed: doesn't matter
                }
                if ($article_socket_response instanceof rex_socket_response && $article_socket_response->isOk()) {
                    // Read article from HTTP request
                    $this->htmlbody = $article_socket_response->getBody();
                }
            }

            // Fallback and default reading method
            if ('' === $this->htmlbody) {
                // Fallback: read article using Redaxo internal method
                if (function_exists('sprogdown')) {
                     $this->htmlbody = sprogdown($article_content->getArticleTemplate());
                } else {
                    $this->htmlbody = $article_content->getArticleTemplate();
                }
            }

            $this->attachments = explode(',', (string) $article->getValue('art_newsletter_attachments'));
            $this->subject = (string) $article->getValue('name');
        }
    }

    /**
     * Updates or inserts the object into database.
     * @return bool true if successful
     */
    public function save()
    {
        $error = true;

        $query = \rex::getTablePrefix() .'375_archive SET '
                    .'article_id = '. $this->article_id .', '
                    .'clang_id = '. $this->clang_id .', '
                    ."subject = '". addslashes(htmlspecialchars($this->subject)) ."', "
                    ."htmlbody = '". base64_encode($this->htmlbody) ."', "
                    ."attachments = '". implode(',', $this->attachments) ."', "
                    ."recipients = '". implode(',', $this->recipients) ."', "
                    ."recipients_failure = '". implode(',', $this->recipients_failure) ."', "
                    ."group_ids = '|". implode('|', $this->group_ids) ."|', "
                    ."sender_email = '". trim($this->sender_email) ."', "
                    ."sender_name = '". addslashes(trim($this->sender_name)) ."', "
                    ."reply_to_email = '". trim($this->reply_to_email) ."', "
                    ."setupdate = '". ('' === $this->setupdate ? date('Y-m-d H:i:s') : $this->setupdate) ."', "
                    ."sentdate = '". $this->sentdate ."', "
                    ."sentby = '". $this->sentby ."' ";
        if (0 === $this->id) {
            $query = 'INSERT INTO '. $query;
        } else {
            $query = 'UPDATE '. $query .' WHERE id = '. $this->id;
        }
        $result = \rex_sql::factory();
        $result->setQuery($query);
        if (0 === $this->id) {
            $this->id = (int) $result->getLastId();
            $error = !$result->hasError();
        }

        return $error;
    }

    /**
     * Sends Newsletter to user.
     * @param User $multinewsletter_user Recipient user
     * @param rex_article $article Redaxo article
     * @return bool true if successful, otherwise false
     */
    private function send($multinewsletter_user, $article = null)
    {
        if (strlen($this->htmlbody) > 0 && strlen($multinewsletter_user->email) > 0) {
            $addon_multinewsletter = rex_addon::get('multinewsletter');

            $mail = new rex_mailer();
            $mail->isHTML(true);
            $mail->CharSet = 'utf-8';
            $mail->From = trim($this->sender_email);
            $mail->FromName = trim($this->sender_name);
            $mail->Sender = trim($this->sender_email);
            if ('' !== $this->reply_to_email) {
                $mail->addReplyTo(trim($this->reply_to_email));
            }
            $mail->addAddress(trim($multinewsletter_user->email), $multinewsletter_user->getName());

            if (1 === (int) $addon_multinewsletter->getConfig('use_smtp')) {
                $mail->Mailer = 'smtp';
                $mail->Host = (string) $addon_multinewsletter->getConfig('smtp_host');
                $mail->Port = (int) $addon_multinewsletter->getConfig('smtp_port');
                $mail->SMTPSecure = (string) $addon_multinewsletter->getConfig('smtp_crypt');
                $mail->SMTPAuth = 1 === (int) $addon_multinewsletter->getConfig('smtp_auth');
                $mail->Username = (string) $addon_multinewsletter->getConfig('smtp_user');
                $mail->Password = (string) $addon_multinewsletter->getConfig('smtp_password');
                // set bcc
                $mail->clearBCCs();
                $bccs = strlen((string) $addon_multinewsletter->getConfig('smtp_bcc')) > 0 ? explode(',', (string) $addon_multinewsletter->getConfig('smtp_bcc')) : [];

                foreach ($bccs as $bcc) {
                    $mail->addBCC($bcc);
                }
            }

            foreach ($this->attachments as $attachment) {
                $media = rex_media::get($attachment);
                if ($media instanceof rex_media) {
                    $mail->addAttachment(rex_path::media($attachment), $attachment);
                }
            }

            $mail->Subject = self::personalize($this->subject, $multinewsletter_user, $article);
            $body = self::personalize($this->htmlbody, $multinewsletter_user, $article);
            $mail->Body = self::replaceURLs($body, $article instanceof rex_article ? $article->getId() : 0, $article instanceof rex_article ? $article->getClangId() : \rex_clang::getCurrentId());
            $success = $mail->send();
            if (!$success) {
                echo rex_view::error(rex_i18n::msg('multinewsletter_archive_recipients_failure') .': '. $multinewsletter_user->email .' - '. $mail->ErrorInfo);
            }
            return $success;
        }

        return false;

    }

    /**
     * Sends newsletter mail to recipient and stores in database.
     * @param User $multinewsletter_user Recipient object
     * @param ?rex_article $article Redaxo article
     * @return bool true, if successful, otherwise false
     */
    public function sendNewsletter($multinewsletter_user, $article = null)
    {
        if ($this->send($multinewsletter_user, $article)) {
            $this->recipients[] = $multinewsletter_user->email;
            $this->sentdate = date('Y-m-d H:i:s');
            $this->save();
            return true;
        }

        $this->recipients_failure[] = $multinewsletter_user->email;
        $this->sentdate = date('Y-m-d H:i:s');
        $this->save();
        return false;

    }

    /**
     * Sends newsletter test mail.
     * @param User $testuser test user object
     * @param int $article_id Redaxo article id
     * @return bool true, if successful, otherwise false
     */
    public function sendTestmail($testuser, $article_id)
    {
        return $this->send($testuser, rex_article::get($article_id));
    }

    /**
     * Sets sendlist archive to autosend and turn on autosend CronJob.
     * @return bool true, if successful
     */
    public function setAutosend()
    {
        $result = rex_sql::factory();
        $result->setQuery('UPDATE '. rex::getTablePrefix() .'375_sendlist SET autosend = 1 WHERE archive_id = '. $this->id);
        if ($result->hasError()) {
            return false;
        }

        // Turn on autosend
        CronjobSender::factory()->activate();

        return true;
    }
}
