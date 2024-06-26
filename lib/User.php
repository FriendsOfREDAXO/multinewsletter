<?php

namespace FriendsOfRedaxo\MultiNewsletter;

use rex;
use rex_addon;
use rex_extension;
use rex_extension_point;
use rex_mailer;
use rex_request;
use rex_sql;

/**
 * Benutzer des MultiNewsletters.
 */
class User
{
    /** @var int Database ID */
    public int $id = 0;

    /** @var string Email address */
    public string $email = '';

    /** @var string Academic degree */
    public string $grad = '';

    /** @var string First name */
    public string $firstname = '';

    /** @var string Last name */
    public string $lastname = '';

    /** @var int Title -1 without, 0 = Mr., 1 = Mrs., 2 = Mx */
    public int $title = -1;

    /** @var int Redaxo language id */
    public int $clang_id = 0;

    /** @var int Status. 0 = inactive, 1 = active, 2 = not verified */
    public int $status = 0;

    /** @var array<int> Array with group ids */
    public array $group_ids = [];

    /** @var string Mailchimp ID */
    public string $mailchimp_id = '';

    /** @var string Create date (format: Y-m-d H:i:s) */
    public string $createdate = '';

    /** @var string Create IP Address */
    public string $createip = '';

    /** @var string Activation date (format: Y-m-d H:i:s) */
    public string $activationdate = '';

    /** @var string Activation IP Address */
    public string $activationip = '';

    /** @var string Activation Key */
    public string $activationkey = '';

    /** @var string Update date (format: Y-m-d H:i:s) */
    public string $updatedate = '';

    /** @var string Update IP Address */
    public string $updateip = '';

    /** @var string Type of subcription, "web", "import", "backend" */
    public string $subscriptiontype = '';

    /** @var int Has privacy policy been accepted? 1 = yes, 0 = no */
    public int $privacy_policy_accepted = 0;

    /**
     * Get user data from database.
     * @param int $user_id user id
     */
    public function __construct($user_id)
    {
        $query = 'SELECT * FROM '. \rex::getTablePrefix() .'375_user WHERE id = '. $user_id;
        $result = \rex_sql::factory();
        $result->setQuery($query);

        if ($result->getRows() > 0) {
            $this->id = (int) $result->getValue('id');
            $this->email = (string) $result->getValue('email');
            $this->grad = (string) $result->getValue('grad');
            $this->firstname = stripslashes((string) $result->getValue('firstname'));
            $this->lastname = stripslashes((string) $result->getValue('lastname'));
            $this->title = (int) $result->getValue('title');
            $this->clang_id = (int) $result->getValue('clang_id');
            $this->status = (int) $result->getValue('status');
            $group_separator = str_contains((string) $result->getValue('group_ids'), '|') ? '|' : ',';
            $group_ids = preg_grep('/^\s*$/s', explode($group_separator, (string) $result->getValue('group_ids')), PREG_GREP_INVERT);
            $this->group_ids = is_array($group_ids) ? array_map('intval', $group_ids) : [];
            $this->mailchimp_id = (string) $result->getValue('mailchimp_id');
            $this->createdate = (string) $result->getValue('createdate');
            $this->createip = (string) $result->getValue('createip');
            $this->activationdate = (string) $result->getValue('activationdate');
            $this->activationip = (string) $result->getValue('activationip');
            $this->activationkey = (string) $result->getValue('activationkey');
            $this->updatedate = (string) $result->getValue('updatedate');
            $this->updateip = (string) $result->getValue('updateip');
            $this->subscriptiontype = (string) $result->getValue('subscriptiontype');
            $this->privacy_policy_accepted = (int) $result->getValue('privacy_policy_accepted');
        }
    }

    /**
     * Create a new user.
     * @param string $email email address
     * @param int $title title
     * @param string $grad academic degree
     * @param string $firstname First name
     * @param string $lastname Last name
     * @param int $clang_id Redaxo clang id
     * @return self initialized user
     */
    public static function factory($email, $title, $grad, $firstname, $lastname, $clang_id)
    {
        $user = self::initByMail($email);
        if(!$user instanceof User) {
            $user = new self(0);
        } 

        $user->email = $email;
        $user->title = $title;
        $user->grad = $grad;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->clang_id = $clang_id;
        $user->status = 1;
        $user->createdate = date('Y-m-d H:i:s');
        $user->createip = rex_request::server('REMOTE_ADDR', 'string');

        return $user;
    }

    /**
     * Activate user.
     */
    public function activate(): void
    {
        $this->activationkey = '0';
        $this->activationdate = date('Y-m-d H:i:s');
        $this->activationip = rex_request::server('REMOTE_ADDR', 'string');
        $this->updatedate = date('Y-m-d H:i:s');
        $this->updateip = rex_request::server('REMOTE_ADDR', 'string');
        $this->status = 1;
        $this->save();

        rex_extension::registerPoint(new rex_extension_point('multinewsletter.userActivated', $this));

        $this->sendAdminNoctificationMail('subscribe');
    }

    /**
     * Delete user.
     */
    public function delete(): void
    {
        if (Mailchimp::isActive()) {
            $Mailchimp = Mailchimp::factory();

            try {
                foreach ($this->group_ids as $group_id) {
                    $group = new Group($group_id);

                    if (strlen($group->mailchimp_list_id) > 0) {
                        $Mailchimp->unsubscribe($this, $group->mailchimp_list_id);
                    }
                }
            } catch (MailchimpException $ex) {
            }
        }

        $sql = rex_sql::factory();
        $sql->setQuery('DELETE FROM '. \rex::getTablePrefix() .'375_user WHERE id = '. $this->id);
    }

    /**
     * Get full name.
     * @return string Name
     */
    public function getName()
    {
        return trim($this->firstname) .' '. trim($this->lastname);
    }

    /**
     * Get archive id(s), that should be sent to user.
     * @param bool $autosend_only if true, only archive IDs with autosend
     * option are returned
     * @return int[] array with Archive IDs that will be sent to user
     */
    public function getSendlistArchiveIDs($autosend_only = false)
    {
        $archive_ids = [];

        $result = rex_sql::factory();
        $result->setQuery('SELECT archive_id FROM '. rex::getTablePrefix() .'375_sendlist '
            . 'WHERE user_id = '. $this->id
            .($autosend_only ? ' AND autosend = 1' : ''));

        for ($i = 0; $result->getRows() > $i; ++$i) {
            $archive_ids[] = (int) $result->getValue('archive_id');
            $result->next();
        }

        return $archive_ids;
    }

    /**
     * Fetch user from database.
     * @param string $email email address
     * @return User|null initialized User object
     */
    public static function initByMail($email)
    {
        $query = 'SELECT * FROM '. \rex::getTablePrefix() ."375_user WHERE email = '". trim($email) ."'";
        $result = \rex_sql::factory();
        $result->setQuery($query);

        if ($result->getRows() > 0) {
            return new self((int) $result->getValue('id'));
        }
        return null;

    }

    /**
     * Personalize activation mail string.
     * @param string $content string to be personalized
     * @return string Personalized string
     */
    private function personalize($content)
    {
        $addon = rex_addon::get('multinewsletter');

        $content = str_replace('+++EMAIL+++', $this->email, stripslashes($content));
        $content = str_replace('+++GRAD+++', htmlspecialchars(stripslashes($this->grad), ENT_QUOTES), $content);
        $content = str_replace('+++LASTNAME+++', htmlspecialchars(stripslashes($this->lastname), ENT_QUOTES), $content);
        $content = str_replace('+++FIRSTNAME+++', htmlspecialchars(stripslashes($this->firstname), ENT_QUOTES), $content);
        $content = str_replace('+++TITLE+++', -1 === $this->title ? '' : htmlspecialchars(stripslashes((string) $addon->getConfig('lang_' . $this->clang_id . '_title_' . $this->title)), ENT_QUOTES), $content);
        $content = (string) preg_replace('/ {2,}/', ' ', $content);

        $subscribe_link = (\rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : \rex::getServer())
            . trim(trim(rex_getUrl((int) $addon->getConfig('link'), $this->clang_id, ['activationkey' => $this->activationkey, 'email' => rawurldecode($this->email)], '&'), '/'), './');
        if (rex_addon::get('yrewrite')->isAvailable()) {
            // Use YRewrite, support for Redaxo installations in subfolders: https://github.com/TobiasKrais/multinewsletter/issues/7
            $subscribe_link = \rex_yrewrite::getFullUrlByArticleId($addon->getConfig('link'), $this->clang_id, ['activationkey' => $this->activationkey, 'email' => rawurldecode($this->email)], '&');
        }
        return str_replace('+++AKTIVIERUNGSLINK+++', $subscribe_link, $content);
    }

    /**
     * Update user in database.
     * @return bool true if error occured
     */
    public function save()
    {
        $error = true;

        $query = \rex::getTablePrefix() .'375_user SET '
                    .'id = '. $this->id .', '
                    ."email = '". trim($this->email) ."', "
                    ."grad = '". $this->grad ."', "
                    ."firstname = '". addslashes($this->firstname) ."', "
                    ."lastname = '". addslashes($this->lastname) ."', "
                    .'title = '. $this->title .', '
                    .'clang_id = '. $this->clang_id .', '
                    .'status = '. $this->status .', '
                    ."group_ids = '|". implode('|', $this->group_ids) ."|', "
                    ."mailchimp_id = '". $this->mailchimp_id ."', "
                    ."createdate = '". ('' === $this->createdate ? date('Y-m-d H:i:s') : $this->createdate) ."', "
                    ."createip = '". ('' === $this->createip ? rex_request::server('REMOTE_ADDR', 'string') : $this->createip) ."', "
                    ."activationdate = '". $this->activationdate ."', "
                    ."activationip = '". $this->activationip ."', "
                    ."activationkey = '". $this->activationkey ."', "
                    ."updatedate = '". date('Y-m-d H:i:s') ."', "
                    ."updateip = '". rex_request::server('REMOTE_ADDR', 'string') ."', "
                    ."subscriptiontype = '". $this->subscriptiontype ."', "
                    .'privacy_policy_accepted = '. $this->privacy_policy_accepted .' ';
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

        // Don't forget Mailchimp
        if (Mailchimp::isActive()) {
            $Mailchimp = Mailchimp::factory();
            $_status = 2 === $this->status ? 'unsubscribed' : (1 === $this->status ? 'subscribed' : 'pending');

            try {
                foreach ($this->group_ids as $group_id) {
                    $group = new Group($group_id);

                    if (strlen($group->mailchimp_list_id) > 0) {
                        $this->mailchimp_id = $Mailchimp->addUserToList($this, $group->mailchimp_list_id, $_status);
                    }
                }
            } catch (MailchimpException $ex) {
            }
        }

        return $error;
    }

    /**
     * Send activation mail.
     * @param string $sender_mail Sender email addresss
     * @param string $sender_name Sender name
     * @param string $subject Mail subject
     * @param string $body Mail content
     * @return bool true if successful, otherwise false
     */
    public function sendActivationMail($sender_mail, $sender_name, $subject, $body)
    {
        if ('' !== $body && strlen($this->email) > 0 && false !== filter_var($sender_mail, FILTER_VALIDATE_EMAIL)) {
            $mail = new rex_mailer();
            $mail->isHTML(true);
            $mail->CharSet = 'utf-8';
            $mail->From = $sender_mail;
            $mail->FromName = $sender_name;
            $mail->Sender = $sender_mail;
            $mail->addAddress($this->email, $this->getName());

            $mail->Subject = $this->personalize($subject);
            $mail->Body = rex_extension::registerPoint(new rex_extension_point('multinewsletter.preSend', $this->personalize($body), [
                'mail' => $mail,
                'user' => $this,
            ]));

            $addon_multinewsletter = rex_addon::get('multinewsletter');
            if (1 === (int) $addon_multinewsletter->getConfig('use_smtp')) {
                $mail->Mailer = 'smtp';
                $mail->Host = (string) $addon_multinewsletter->getConfig('smtp_host');
                $mail->Port = (int) $addon_multinewsletter->getConfig('smtp_port');
                $mail->SMTPSecure = (string) $addon_multinewsletter->getConfig('smtp_crypt');
                $mail->SMTPAuth = (bool) $addon_multinewsletter->getConfig('smtp_auth');
                $mail->Username = (string) $addon_multinewsletter->getConfig('smtp_user');
                $mail->Password = (string) $addon_multinewsletter->getConfig('smtp_password');
            }

            return $mail->send();
        }

        return false;

    }

    /**
     * Send admin mail with hint, that user status changed.
     * @param string $type Either "subscribe" or "unsubscribe"
     * @return bool true if successful, otherwise false
     */
    public function sendAdminNoctificationMail($type)
    {
        $addon = rex_addon::get('multinewsletter');

        if (false !== filter_var($addon->getConfig('subscribe_meldung_email'), FILTER_VALIDATE_EMAIL)) {
            $mail = new rex_mailer();
            $mail->isHTML(true);
            $mail->CharSet = 'utf-8';
            $mail->From = (string) $addon->getConfig('sender');
            $mail->FromName = (string) $addon->getConfig('lang_' . $this->clang_id . '_sendername');
            $mail->Sender = (string) $addon->getConfig('sender');

            $mail->addAddress((string) $addon->getConfig('subscribe_meldung_email'));

            if ('subscribe' === $type) {
                $mail->Subject = 'Neue Anmeldung zum Newsletter';
                $mail->Body = 'Neue Anmeldung zum Newsletter: ' . $this->email;
            } else {
                $mail->Subject = 'Abmeldung vom Newsletter';
                $mail->Body = 'Abmeldung vom Newsletter: ' . $this->email;
            }

            $addon_multinewsletter = rex_addon::get('multinewsletter');
            if (1 === (int) $addon_multinewsletter->getConfig('use_smtp')) {
                $mail->Mailer = 'smtp';
                $mail->Host = (string) $addon_multinewsletter->getConfig('smtp_host');
                $mail->Port = (int) $addon_multinewsletter->getConfig('smtp_port');
                $mail->SMTPSecure = (string) $addon_multinewsletter->getConfig('smtp_crypt');
                $mail->SMTPAuth = (bool) $addon_multinewsletter->getConfig('smtp_auth');
                $mail->Username = (string) $addon_multinewsletter->getConfig('smtp_user');
                $mail->Password = (string) $addon_multinewsletter->getConfig('smtp_password');
            }

            return $mail->send();
        }

        return false;

    }

    /**
     * Unsubcribe user.
     * @param string $action Either "delete" or "status_unsubscribed"
     */
    public function unsubscribe($action = 'delete'): void
    {
        if ('delete' === $action) {
            $this->delete();
        } else {
            // $action = "status_unsubscribed"
            $this->status = 2;
            $this->save();
        }

        $this->sendAdminNoctificationMail('unsubscribe');
    }
}
