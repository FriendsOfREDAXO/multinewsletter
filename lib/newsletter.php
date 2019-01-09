<?php

/**
 * MultiNewsletter Newletter (in der Datenbank als Newsletter Archiv).
 *
 * @author Tobias Krais
 */
class MultinewsletterNewsletter {
	/**
	 * @var int Database ID
	 */
	var $id = 0;
	
	/**
	 * @var int Redaxo article id
	 */
	var $article_id = 0;
	
	/**
	 * @var int Redaxo language id
	 */
	var $clang_id = 0;
	
	/**
	 * @var string Subject
	 */
	var $subject = "";
	
	/**
	 * @var string Body
	 */
	var $htmlbody = "";
	
	/**
	 * @var string[] Array with attachment file names
	 */
	var $attachments = [];
	
	/**
	 * @var string[] Array with recipient email addresses
	 */
	var $recipients = [];
	
	/**
	 * @var string[] Array with recipient email addresses that failed to send
	 */
	var $recipients_failure = [];
	
	/**
	 * @var string[] Array with group ids
	 */
	var $group_ids = [];
	
	/**
	 * @var string Sender email address
	 */
	var $sender_email = "";

	/**
	 * @var string Sender name
	 */
	var $sender_name = "";

	/**
	 * @var string Setup date (format: Y-m-d H:i:s)
	 */
	var $setupdate = "";

	/**
	 * @var string Send date (format: Y-m-d H:i:s)
	 */
	var $sentdate = "";
	
	/**
	 * @var string Redaxo send user name
	 */
	var $sentby = "";
	
	/**
	 * @var int Number of remaining users in sendlist
	 */
	var $remaining_users = 0;
	
    /**
     * Gets object data from database
     * @param int $id Archive ID
     */
    public function __construct($id) {
		$query = "SELECT * FROM ". \rex::getTablePrefix() ."375_archive WHERE id = ". $id;
		$result = \rex_sql::factory();
		$result->setQuery($query);

		if ($result->getRows() > 0) {
			$this->id = $result->getValue("id");
			$this->article_id = $result->getValue("article_id");
			$this->clang_id = $result->getValue("clang_id");
			$this->subject = htmlspecialchars_decode($result->getValue("subject"));
			$this->htmlbody = base64_decode($result->getValue("htmlbody"));
			$attachment_separator = strpos($result->getValue("attachments"), '|') !== FALSE ? "|" : ",";
			$this->attachments = preg_grep('/^\s*$/s', explode($attachment_separator, $result->getValue("attachments")), PREG_GREP_INVERT);
			$recipients_separator = strpos($result->getValue("recipients"), '|') !== FALSE ? "|" : ",";
			$this->recipients = preg_grep('/^\s*$/s', explode($recipients_separator, $result->getValue("recipients")), PREG_GREP_INVERT);
			$this->recipients_failure = preg_grep('/^\s*$/s', explode(",", $result->getValue("recipients_failure")), PREG_GREP_INVERT);
			$this->group_ids = preg_grep('/^\s*$/s', explode("|", $result->getValue("group_ids")), PREG_GREP_INVERT);
			$this->sender_email = $result->getValue("sender_email");
			$this->sender_name = $result->getValue("sender_name");
			$this->setupdate = $result->getValue("setupdate");
			$this->sentdate = $result->getValue("sentdate");
			$this->sentby = $result->getValue("sentby");
		}
    }

    /**
     * Counts remaining users in sendlist
     * @return int Number of remaining unsers
     */
    public function countRemainingUsers() {
        if ($this->remaining_users == 0) {
            $query = "SELECT COUNT(*) as total FROM " . rex::getTablePrefix() . "375_sendlist "
				."WHERE archive_id = ". $this->id;
            $result = rex_sql::factory();
            $result->setQuery($query);

            return $result->getValue("total");
        }
        else {
            return $this->remaining_users;
        }
    }

    /**
     * Creates a new newsletter archive
     * @param int $article_id Redaxo article id
     * @param int $clang_id Redaxo clang id
     * @return MultinewsletterNewsletter Initialized MultiNewsletter object.
     */
    public static function factory($article_id, $clang_id = null) {
        // init Mailbody and subject
        $newsletter = new self(0);
        $newsletter->readArticle($article_id, $clang_id);

        return $newsletter;
    }

    /**
     * Deletes archive
     */
    public function delete() {
        $sql = rex_sql::factory();
		$sql->setQuery("DELETE FROM ". \rex::getTablePrefix() ."375_archive WHERE id = ". $$this->id);
    }

    /**
     * Personalizes a string
     * @param string $content Content that has to be personalized
     * @param MultinewsletterUser $user Recipient user object
	 * @param rex_article Redaxo article
     * @return string Personalized string
     */
    public static function personalize($content, $user, $article = null) {
		return preg_replace('/ {2,}/', ' ', self::replaceVars($content, $user, $article));
    }

	/**
	 * Get article full URL, including domain
	 * @param int $id Redaxo article id
	 * @param int $clang Redaxo clang id
	 * @param string[] $params URL parameters
	 * @return string
	 */
    public static function getUrl($id = null, $clang = null, array $params = []) {
		$url = "";
        if (rex_addon::get('yrewrite') && rex_addon::get('yrewrite')->isAvailable()) {
            $url = rex_getUrl($id, $clang, $params);
        }
        else {
            $url = rtrim(rex::getServer(), '/') . '/' . ltrim(str_replace(['../', './'], '', rex_getUrl($id, $clang, $params)), '/');
        }
        return $url;
    }

    /**
     * Corrects URLs in content string
     * @param string $content Content
     * @return string String with corrected URLs
     */
    public static function replaceURLs($content) {
		$content = str_replace('href="/', 'href="'. (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace('href="./', 'href="'. (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace('href="../', 'href="'. (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);

		$content = str_replace("href='/", "href='". (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace("href='./", "href='". (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace("href='../", "href='". (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);

		$content = str_replace('src="/', 'src="'. (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace('src="./', 'src="'. (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace('src="../', 'src="'. (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);

		$content = str_replace("src='/", "src='". (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace("src='./", "src='". (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace("src='../", "src='". (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);

		$content = str_replace("src='index.php", "src='". (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()) .'index.php', $content);
		$content = str_replace('src="index.php', 'src="'. (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()) .'index.php', $content);
		
		// Correct image URLs
		$content = str_replace('&amp;', '&', $content);
		
		return $content;
    }

	/**
     * Personalized string
     * @param string $content Content
     * @param MultinewsletterUser $user Recipient user object
	 * @param rex_article $article Redaxo article
     * @return string Personalized content
     */
    public static function replaceVars($content, $user, $article = null) {
        $addon = rex_addon::get("multinewsletter");
		$clang_id = $user->clang_id > 0 ? $user->clang_id : rex_clang::getCurrentId();

        $replaces  = [
			'+++GRAD+++' => $user->grad,
			'+++FIRSTNAME+++' => $user->firstname,
			'+++LASTNAME+++' => $user->lastname,
			'+++EMAIL+++' => $user->email
		];

        return strtr($content, rex_extension::registerPoint(
			new rex_extension_point(
				'multinewsletter.replaceVars', array_merge(
					$replaces, [
						'+++TITLE+++'				=> $addon->getConfig('lang_' . $clang_id . "_title_" . $user->title),
						'+++ABMELDELINK+++'			=> self::getUrl($addon->getConfig('link_abmeldung'), $clang_id, ['unsubscribe' => $user->email,'key'=>$user->activationkey]),
						'+++AKTIVIERUNGSLINK+++'	=> self::getUrl($addon->getConfig('link'), $clang_id, ['activationkey' => $user->activationkey, 'email' => $user->email]),
						'+++NEWSLETTERLINK+++'		=> $article ? self::getUrl($article->getId(), $clang_id) : '',
						'+++LINK_PRIVACY_POLICY+++'	=> rex_getUrl(rex_config::get('d2u_helper', 'article_id_privacy_policy', rex_article::getSiteStartArticleId())),
						'+++LINK_IMPRESS+++'		=> rex_getUrl(rex_config::get('d2u_helper', 'article_id_impress', rex_article::getSiteStartArticleId())),
					])
				)
			)
		);
    }
	
	/**
	 * Get fallback lang settings
	 * @param int $fallback_lang
	 * @return int rex_clang fallback clang_id
	 */
    public static function getFallbackLang($fallback_lang = null) {
        $addon = rex_addon::get("multinewsletter");

        if($addon->getConfig("lang_fallback", 0) == 0 && !is_null($fallback_lang)) {
            return $fallback_lang;
		}

        if($addon->getConfig("lang_fallback", 0) == 0) {
            return null;
		}

        return rex_config::get("d2u_helper", "default_lang", $fallback_lang);
    }

    /**
     * Reads a redaxo article in this object
     * @param int $article_id Redaxo article id
     * @param int $clang_id Redaxo clang id
     */
    private function readArticle($article_id, $clang_id) {
        $article = rex_article::get($article_id, $clang_id);
        $article_content = new rex_article_content($article_id, $clang_id);

        if ($article instanceof rex_article && $article->isOnline()) {
            $this->article_id = $article_id;
            $this->clang_id = $clang_id;
            $this->htmlbody = $article_content->getArticleTemplate();
            $this->attachments = explode(",", $article->getValue('art_newsletter_attachments'));
            $this->subject = $article->getValue('name');
        }
    }

	/**
	 * Updates or inserts the object into database.
	 * @return boolean TRUE if successful
	 */
	public function save() {
		$error = TRUE;

		$query = \rex::getTablePrefix() ."375_archive SET "
					."article_id = ". $this->article_id .", "
					."clang_id = ". $this->clang_id .", "
					."subject = '". htmlspecialchars($this->subject) ."', "
					."htmlbody = '". base64_encode($this->htmlbody) ."', "
					."attachments = '". implode(",", array_filter($this->attachments)) ."', "
					."recipients = '". implode(",", $this->recipients) ."', "
					."recipients_failure = '". implode(",", $this->recipients_failure) ."', "
					."group_ids = '|". implode("|", $this->group_ids) ."|', "
					."sender_email = '". trim($this->sender_email) ."', "
					."sender_name = '". trim($this->sender_name) ."', "
					."setupdate = '". ($this->setupdate == "" ? date('Y-m-d H:i:s') : $this->setupdate) ."', "
					."sentdate = '". $this->sentdate ."', "
					."sentby = '". $this->sentby ."' ";
		if($this->id == 0) {
			$query = "INSERT INTO ". $query;
		}
		else {
			$query = "UPDATE ". $query ." WHERE id = ". $this->id;
		}
		$result = \rex_sql::factory();
		$result->setQuery($query);
		if($this->id == 0) {
			$this->id = $result->getLastId();
			$error = !$result->hasError();
		}

		return $error;
    }

    /**
     * Sends Newsletter to user
     * @param MultinewsletterUser $multinewsletter_user Recipient user
     * @param rex_article $article Redaxo article
     * @return boolean TRUE if successful, otherwise FALSE
     */
    private function send($multinewsletter_user, $article = null) {
        if (strlen($this->htmlbody) && strlen($multinewsletter_user->email)) {
            $addon_multinewsletter = rex_addon::get("multinewsletter");

            $mail = new rex_mailer();
            $mail->IsHTML(TRUE);
            $mail->CharSet  = "utf-8";
            $mail->From = "info@inotec-gmbh.de";
            $mail->FromName = trim($this->sender_name);
            $mail->Sender = trim($this->sender_email);
            $mail->From = trim($this->sender_email);
            $mail->FromName = trim($this->sender_name);
            $mail->Sender = trim($this->sender_email);
            $mail->AddAddress(trim($multinewsletter_user->email), $multinewsletter_user->getName());

            if ($addon_multinewsletter->getConfig('use_smtp')) {
                $mail->Mailer = 'smtp';
                $mail->Host = $addon_multinewsletter->getConfig('smtp_host');
                $mail->Port = $addon_multinewsletter->getConfig('smtp_port');
                $mail->SMTPSecure = $addon_multinewsletter->getConfig('smtp_crypt');
                $mail->SMTPAuth = $addon_multinewsletter->getConfig('smtp_auth');
                $mail->Username = $addon_multinewsletter->getConfig('smtp_user');
                $mail->Password = $addon_multinewsletter->getConfig('smtp_password');
                // set bcc
                $mail->clearBCCs();
                $bccs = strlen($addon_multinewsletter->getConfig('smtp_bcc')) ? explode(',', $addon_multinewsletter->getConfig('smtp_bcc')) : [];

                foreach ($bccs as $bcc) {
                    $mail->addBCC($bcc);
                }
            }

            foreach ($this->attachments as $attachment) {
                $media = rex_media::get($attachment);
				if($media instanceof rex_media) {
					$mail->addAttachment(rex_path::media($attachment), $media->getTitle());
				}
            }

            $mail->Subject = self::personalize($this->subject, $multinewsletter_user, $article);
			$body = self::personalize($this->htmlbody, $multinewsletter_user, $article);
            $mail->Body = self::replaceURLs($body);
            $success = $mail->send();
			if(!$success) {
				print rex_view::error(rex_i18n::msg('multinewsletter_archive_recipients_failure') .": ". $multinewsletter_user->email ." - ". $mail->ErrorInfo);
			}
			return $success;
        }
        else {
            return FALSE;
        }
    }

    /**
     * Sends newsletter mail to recipient and stores in database
     * @param MultinewsletterUser $user Recipient object
     * @return boolean TRUE, if successful, otherwise FALSE
     */
    public function sendNewsletter($multinewsletter_user, $article = null) {
        if ($this->send($multinewsletter_user, $article)) {
			$this->recipients[] = $multinewsletter_user->email;
			$this->sentdate = date('Y-m-d H:i:s');
			$this->save();
            return TRUE;
        }
		else {
			$this->recipients_failure[] = $multinewsletter_user->email;
			$this->sentdate = date('Y-m-d H:i:s');
			$this->save();
	        return FALSE;
		}
    }

    /**
     * Sends newsletter test mail
     * @param MultinewsletterUser $testuser test user object
     * @param int $article_id Redaxo article id
     * @return boolean TRUE, if successful, otherwise FALSE
     */
    public function sendTestmail($testuser, $article_id) {
        return $this->send($testuser, rex_article::get($article_id));
    }

    /**
     * Sets sendlist archive to autosend and turn on autosend CronJob
     * @return boolean TRUE, if successful
     */
    public function setAutosend() {
	    $result = rex_sql::factory();
		$result->setQuery("UPDATE ". rex::getTablePrefix() ."375_sendlist SET autosend = 1 WHERE archive_id = ". $this->id);
		if($result->hasError()){
			return FALSE;
		}
		
		// Turn on autosend
		multinewsletter_cronjob_sender::factory()->activate();
		
        return TRUE;
    }
}
