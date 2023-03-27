<?php

/**
 * Benutzer des MultiNewsletters.
 */
class MultinewsletterUser
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

    /** @var int Title 0 = male, 1 = female */
    public int $title = 0;

    /** @var int Redaxo language id */
    public int $clang_id = 0;

    /** @var int Status. 0, = inactive, 1 =  active, 2 = not verified */
    public int $status = 0;

    /** @var array<int> Array with group ids */
    public array $group_ids = [];

    /** @var string Mailchimp ID */
    public $mailchimp_id = '';

    /** @var string Create date (format: Y-m-d H:i:s) */
    public string $createdate = '';

    /** @var string Create IP Address */
    public string $createip = '';

    /** @var string Activation date (format: Y-m-d H:i:s) */
    public $activationdate = '';

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
     * @param int $title title (0 = male, 1 = female)
     * @param string $grad academic degree
     * @param string $firstname First name
     * @param string $lastname Last name
     * @param int $clang_id Redaxo clang id
     * @return MultinewsletterUser initialized user
     */
    public static function factory($email, $title, $grad, $firstname, $lastname, $clang_id)
    {
        $user = self::initByMail($email) ?: new self(0);

        $user->email = $email;
        $user->title = $title;
        $user->grad = $grad;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->clang_id = $clang_id;
        $user->status = 1;
        $user->createdate = date('Y-m-d H:i:s');
        $user->createip = $_SERVER['REMOTE_ADDR'];

        return $user;
    }

    /**
     * Activate user.
     */
    public function activate()
    {
        $this->activationkey = '0';
        $this->activationdate = date('Y-m-d H:i:s');
        $this->activationip = $_SERVER['REMOTE_ADDR'];
        $this->updatedate = date('Y-m-d H:i:s');
        $this->updateip = $_SERVER['REMOTE_ADDR'];
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
        if (MultinewsletterMailchimp::isActive()) {
            $Mailchimp = MultinewsletterMailchimp::factory();

            try {
                foreach ($this->group_ids as $group_id) {
                    $group = new MultinewsletterGroup($group_id);

                    if (strlen($group->mailchimp_list_id)) {
                        $Mailchimp->unsubscribe($this, $group->mailchimp_list_id);
                    }
                }
            } catch (MultinewsletterMailchimpException $ex) {
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
            $archive_ids[] = $result->getValue('archive_id');
            $result->next();
        }

        return $archive_ids;
    }

    /**
     * Fetch user from database.
     * @param string $email email address
     * @return MultinewsletterUser|bool initialized MultinewsletterUser object
     */
    public static function initByMail($email)
    {
        $query = 'SELECT * FROM '. \rex::getTablePrefix() ."375_user WHERE email = '". trim($email) ."'";
        $result = \rex_sql::factory();
        $result->setQuery($query);

        if ($result->getRows() > 0) {
            return new self($result->getValue('id'));
        }
        return false;

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
        $content = str_replace('+++TITLE+++', htmlspecialchars(stripslashes($addon->getConfig('lang_' . $this->clang_id . '_title_' . $this->title)), ENT_QUOTES), $content);
        $content = preg_replace('/ {2,}/', ' ', $content);

        $subscribe_link = (\rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : \rex::getServer())
            . trim(trim(rex_getUrl($addon->getConfig('link'), $this->clang_id, ['activationkey' => $this->activationkey, 'email' => rawurldecode($this->email)], '&'), '/'), './');
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
                    .'title = '. ('' == $this->title ? 0 : $this->title) .', '
                    .'clang_id = '. $this->clang_id .', '
                    .'status = '. $this->status .', '
                    ."group_ids = '|". implode('|', (array) $this->group_ids) ."|', "
                    ."mailchimp_id = '". $this->mailchimp_id ."', "
                    ."createdate = '". ('' == $this->createdate ? date('Y-m-d H:i:s') : $this->createdate) ."', "
                    ."createip = '". ('' == $this->createip ? $_SERVER['REMOTE_ADDR'] : $this->createip) ."', "
                    ."activationdate = '". $this->activationdate ."', "
                    ."activationip = '". $this->activationip ."', "
                    ."activationkey = '". $this->activationkey ."', "
                    ."updatedate = '". date('Y-m-d H:i:s') ."', "
                    ."updateip = '". $_SERVER['REMOTE_ADDR'] ."', "
                    ."subscriptiontype = '". $this->subscriptiontype ."', "
                    .'privacy_policy_accepted = '. $this->privacy_policy_accepted .' ';
        if (0 == $this->id) {
            $query = 'INSERT INTO '. $query;
        } else {
            $query = 'UPDATE '. $query .' WHERE id = '. $this->id;
        }
        $result = \rex_sql::factory();
        $result->setQuery($query);
        if (0 == $this->id) {
            $this->id = (int) $result->getLastId();
            $error = !$result->hasError();
        }

        // Don't forget Mailchimp
        if (MultinewsletterMailchimp::isActive()) {
            $Mailchimp = MultinewsletterMailchimp::factory();
            $_status = 2 == $this->status ? 'unsubscribed' : (1 == $this->status ? 'subscribed' : 'pending');

            try {
                foreach ($this->group_ids as $group_id) {
                    $group = new MultinewsletterGroup($group_id);

                    if (strlen($group->mailchimp_list_id)) {
                        $result = $Mailchimp->addUserToList($this, $group->mailchimp_list_id, $_status);
                        $this->mailchimp_id = $result['id'];
                    }
                }
            } catch (MultinewsletterMailchimpException $ex) {
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
        if (!empty($body) && strlen($this->email) && false !== filter_var($sender_mail, FILTER_VALIDATE_EMAIL)) {
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
            if ($addon_multinewsletter->getConfig('use_smtp')) {
                $mail->Mailer = 'smtp';
                $mail->Host = $addon_multinewsletter->getConfig('smtp_host');
                $mail->Port = $addon_multinewsletter->getConfig('smtp_port');
                $mail->SMTPSecure = $addon_multinewsletter->getConfig('smtp_crypt');
                $mail->SMTPAuth = $addon_multinewsletter->getConfig('smtp_auth');
                $mail->Username = $addon_multinewsletter->getConfig('smtp_user');
                $mail->Password = $addon_multinewsletter->getConfig('smtp_password');
            }

            return $mail->Send();
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
            $mail->From = $addon->getConfig('sender');
            $mail->FromName = $addon->getConfig('lang_' . $this->clang_id . '_sendername');
            $mail->Sender = $addon->getConfig('sender');

            $mail->addAddress($addon->getConfig('subscribe_meldung_email'));

            if ('subscribe' == $type) {
                $mail->Subject = 'Neue Anmeldung zum Newsletter';
                $mail->Body = 'Neue Anmeldung zum Newsletter: ' . $this->email;
            } else {
                $mail->Subject = 'Abmeldung vom Newsletter';
                $mail->Body = 'Abmeldung vom Newsletter: ' . $this->email;
            }

            $addon_multinewsletter = rex_addon::get('multinewsletter');
            if ($addon_multinewsletter->getConfig('use_smtp')) {
                $mail->Mailer = 'smtp';
                $mail->Host = $addon_multinewsletter->getConfig('smtp_host');
                $mail->Port = $addon_multinewsletter->getConfig('smtp_port');
                $mail->SMTPSecure = $addon_multinewsletter->getConfig('smtp_crypt');
                $mail->SMTPAuth = $addon_multinewsletter->getConfig('smtp_auth');
                $mail->Username = $addon_multinewsletter->getConfig('smtp_user');
                $mail->Password = $addon_multinewsletter->getConfig('smtp_password');
            }

            return $mail->Send();
        }

        return false;

    }

    /**
     * Unsubcribe user.
     */
    public function unsubscribe($action = 'delete')
    {
        if ('delete' == $action) {
            $this->delete();
        } else {
            // $action = "status_unsubscribed"
            $this->status = 2;
            $this->save();
        }

        $this->sendAdminNoctificationMail('unsubscribe');
    }
}
