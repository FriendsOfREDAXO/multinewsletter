<?php

/**
 * MultiNewsletter Newletter (noch zu versenden)
 *
 * @author Tobias Krais
 */
class MultinewsletterNewsletterManager {
    /**
     * @var MultinewsletterNewsletter[] Archiv Objekte des Newsletters. ACHTUNG: der Index im Array
     * muss die Archiv ID sein.
     */
    var $archives = [];

    /**
     * @var MultinewsletterUser[] Empfänger des Newsletters.
     */
    var $recipients = [];

    /**
     * @var boolean $autosend_only TRUE if only autosend archives and receipients
	 * should be managed.
     */
    var $autosend_only = FALSE;

    /**
     * @var MultinewsletterUser[] Users an die der Newsletter zuletzt versand wurde.
     */
    var $last_send_users = [];

    /**
     * @var int Anzahl ausstehender Newsletter Mails
     */
    var $remaining_users = 0;

    /**
     * Stellt die Daten des Newsletters aus einem Archiv zusammen.
     * @param int $numberMails Anzahl der Mails für den nächsten Versandschritt.
	 * @param boolean $autosend_only Init only reciepients with autosend option.
     */
    public function __construct($numberMails = 0, $autosend_only = FALSE) {
		$this->autosend_only = $autosend_only;
        $this->initArchivesToSend();
        $this->initRecipients($numberMails);
    }

	/**
     * Cleans up (deletes all recipients in archives that are older than 4 weeks.
	 * Deletes also all recipients that did not activate their subscription within
	 * last 4 weeks.
     */
	public static function autoCleanup() {
		// Cleanup archives
        $query = "SELECT id FROM ". rex::getTablePrefix() ."375_archive "
			."WHERE sentdate < '". date('Y-m-d H:i:s', strtotime('-4 weeks')) ."' "
				."AND recipients NOT LIKE '%Addresses deleted.%'";
        $result = rex_sql::factory();
        $result->setQuery($query);

		for ($i = 0; $result->getRows() > $i; $i++) {
            $newsletter = new MultinewsletterNewsletter($result->getValue('id'));
			$newsletter->recipients = [count($newsletter->recipients) ." recipients. Addresses deleted."];
			$newsletter->recipients_failure = [count($newsletter->recipients_failure) ." recipients with send failure. Addresses deleted."];
			$newsletter->save();
			print rex_view::success("Newsletter '". $newsletter->subject ."' recipient addresses deleted.". PHP_EOL);
			
            $result->next();
        }
		
		// Cleanup not activated users 
        $query = "SELECT id FROM ". rex::getTablePrefix() ."375_user "
			."WHERE (activationkey IS NOT NULL AND activationkey != '' AND activationkey != '0') AND createdate < '". date('Y-m-d H:i:s', strtotime('-4 weeks')) ."'";
		$result->setQuery($query);
		for ($i = 0; $result->getRows() > $i; $i++) {
            $user = new MultinewsletterUser($result->getValue('id'));
			$user->delete();
			print rex_view::success($user->email ." deleted, because not activated for more than 4 weeks.". PHP_EOL);
			
            $result->next();
        }
	}
	
	/**
     * Versendet einen Newsletter sofort.
     * @param int[] $group_ids Array mit den GruppenIDs der vorzubereitenden Gruppen.
     * @param int $article_id ID des zu versendenden Redaxo Artikels
     * @param int $fallback_clang_id ID der Sprache, die verwendet werden soll,
     * wenn der Artikel offline ist.
     * @param int[] $recipient_ids IDs of receipients that should be added to send list
     * @param string $attachments Attachment list, comma separated
     * @return TRUE if successful started, otherwise FALSE
     */
	public static function autosend($group_ids, $article_id, $fallback_clang_id, array $recipient_ids = [], $attachments = '') {
		$newsletterManager = MultinewsletterNewsletterManager::factory();
		$newsletterManager->autosend_only = TRUE;
		$newsletterManager->prepare($group_ids, $article_id, $fallback_clang_id, $recipient_ids, $attachments);

		$cronjob_sender = multinewsletter_cronjob_sender::factory();
		if($cronjob_sender->isInstalled() && count($newsletterManager->archives) > 0) {
			// Activate Cronjob
			$cronjob_sender->activate();
			return TRUE;
		}
		else {
			// Send it all right now - or try it at least
			$sendresult = $newsletterManager->send(count($newsletterManager->recipients));
			// Send final admin notification
			foreach($newsletterManager->archives as $archive) {
				if($archive->countRemainingUsers() == 0) {
					$subject = 'Versand Newsletter abgeschlossen';
					$body = 'Der Versand das folgenden Newsletters wurde abgeschlossen:<br>'
						. $archive->subject ."<br><br>"
						. "Der Versand per Cronjob war nicht möglich und wurde daher ohne Berücksichtigung möglicher Serverlimits auf ein mal durchgeführt. Bitte installieren Sie das Cronjob Addon und aktivieren Sie außerdem den MutliNewsletter Sender Cronjob über die Einstellungen des MultiNewsletters.<br><br>"
						. "Fehler gab es beim Versand an folgende Nutzer:<br>"
						. implode(", ", $archive->recipients_failure);
					$newsletterManager->sendAdminNotification($subject, $body);
					// Unset archive
					unset($this->archives[$archive->id]);
				}
			}
		}
	}
	
    /**
     * Sends next step of newletters in send list.
     */
	public static function cronSend() {
		// Calculate maximum mails per Cronjob step (every 5 minutes)
		$numberMails = round(rex_config::get('multinewsletter', 'max_mails') * rex_config::get('multinewsletter', 'versandschritte_nacheinander') * 3600 / rex_config::get('multinewsletter', 'sekunden_pause') / 12);
		$newsletterManager = new MultinewsletterNewsletterManager($numberMails, TRUE);
		$newsletterManager->send($numberMails);

		// Send final admin notification
		foreach($newsletterManager->archives as $archive) {
			if($archive->countRemainingUsers() == 0) {
				$subject = 'Versand Newsletter abgeschlossen';
				$body = 'Der automatisierte Versand des folgenden Newsletters wurde abgeschlossen:<br>'
					.'<b>'. $archive->subject .'</b>'
					.'<br><br>Anzahl erfolgreich versendete Empfänger: '. count($archive->recipients);
				if(count($archive->recipients_failure) > 0) {
					$body .= "<br><br>Fehler gab es beim Versand an folgende Nutzer:<br>- "
						. implode('<br>- ', $archive->recipients_failure);
				}
				$body .= "<br><br>Details finden Sie in den Archiven des MultiNewsletters und im Cronjob Log.";
				$newsletterManager->sendAdminNotification($subject, $body);
				// Unset archive
				unset($newsletterManager->archives[$archive->id]);
			}
		}

		// Deactivate Cronjob
		if(count($newsletterManager->archives) == 0) {
			multinewsletter_cronjob_sender::factory()->deactivate();
		}
		
		print rex_view::success("Step completed.");
	}

	/**
	 * Get newsletter archives which are on send list.
	 * @param boolean $manual_send_only If TRUE, autosend archives are excluded.
	 * @return \MultinewsletterNewsletter[] Array with MultinewsletterNewsletter archives
	 */
	public static function getArchivesToSend($manual_send_only = true) {
		$query = "SELECT archive_id FROM ". rex::getTablePrefix() ."375_sendlist "
			.($manual_send_only ? "WHERE autosend = 0 " : "")
			."GROUP BY archive_id";
        $result = rex_sql::factory();
        $result->setQuery($query);
		
		$newsletter_archives = [];
		for ($i = 0; $result->getRows() > $i; $i++) {
            $newsletter_archives[] = new MultinewsletterNewsletter($result->getValue('archive_id'));

			$result->next();
        }

		return $newsletter_archives;
	}

	/**
	 * Creates a blank, uninitialized MultinewsletterNewsletterManager object.
	 * @return MultinewsletterNewsletterManager Empty MultinewsletterNewsletterManager object.
	 */
	public static function factory() {
		$manager = new MultinewsletterNewsletterManager();
		$manager->archives = [];
		$manager->recipients = [];
		return $manager;
	}
	
    /**
     * Initialisiert die Newsletter Archive, die zum Versand ausstehen.
     */
    private function initArchivesToSend() {
        $query = "SELECT archive_id FROM ". rex::getTablePrefix() ."375_sendlist "
			.($this->autosend_only ? "WHERE autosend = 1 ": "")
			."GROUP BY archive_id";
        $result = rex_sql::factory();
        $result->setQuery($query);
        $num_rows = $result->getRows();

        for ($i = 0; $num_rows > $i; $i++) {
            $archive_id = $result->getValue('archive_id');
            $this->archives[$archive_id] = new MultinewsletterNewsletter($archive_id);
            $result->next();
        }
    }

    /**
     * Initialisiert die Newsletter Empfänger, die zum Versand ausstehen.
     * @param int $numberMails Anzahl der Mails für den nächsten Versandschritt.
     */
    private function initRecipients($numberMails = 0) {
        $query = "SELECT id FROM " . rex::getTablePrefix() . "375_sendlist AS sendlist "
			. "LEFT JOIN " . rex::getTablePrefix() . "375_user AS users "
				. "ON sendlist.user_id = users.id "
			. "WHERE id > 0 "
			.($this->autosend_only ? "AND autosend = 1 ": "")
			. "ORDER BY archive_id, email";
        if ($numberMails > 0) {
            $query .= " LIMIT 0, " . $numberMails;
        }
		$result = rex_sql::factory();
        $result->setQuery($query);
        $num_rows = $result->getRows();
        for ($i = 0; $num_rows > $i; $i++) {
            $this->recipients[] = new MultinewsletterUser($result->getValue('id'));
            $result->next();
        }
    }

    /**
     * Returns pending user number. If autosend_only in this object is TRUE,
	 * only autosend number is returned, otherwise only not autosend user number
	 * is returned
     * @return int Pending user number
     */
    public function countRemainingUsers() {
        if ($this->remaining_users == 0) {
            $query = "SELECT COUNT(*) as total FROM " . rex::getTablePrefix() . "375_sendlist"
				." WHERE autosend = ". ($this->autosend_only ? "1" : "0");
            $result = rex_sql::factory();
            $result->setQuery($query);

            return $result->getValue("total");
        }
        else {
            return $this->remaining_users;
        }
    }

    /**
     * Bereitet den Versand des Newsletters vor.
     * @param int[] $group_ids Array mit den GruppenIDs der vorzubereitenden Gruppen.
     * @param int $article_id ID des zu versendenden Redaxo Artikels
     * @param int $fallback_clang_id ID der Sprache, die verwendet werden soll,
     * wenn der Artikel offline ist.
     * @param int[] $recipient_ids IDs of receipients that should be added to send list
     * @param string $attachments Attachment list, comma separated
     * @return int[] Array mit den Sprach IDs, die Offline sind und durch die
     * Fallback Sprache ersetzt wurden.
     */
    public function prepare($group_ids, $article_id, $fallback_clang_id, array $recipient_ids = [], $attachments = '') {
        $offline_lang_ids = [];

        $clang_ids = [];
        // Welche Sprachen sprechen die Nutzer der vorzubereitenden Gruppen?
        $where_groups = [];
        foreach ($group_ids as $group_id) {
            $where_groups[] = "
                group_ids = '" . $group_id . "' OR 
                group_ids LIKE '" . $group_id . "|%' OR 
                group_ids LIKE '%|" . $group_id . "' OR 
                group_ids LIKE '%|" . $group_id . "|%' OR 
                group_ids LIKE '" . $group_id . ",%' OR 
                group_ids LIKE '%," . $group_id . "' OR 
                group_ids LIKE '%," . $group_id . ",%' 
            ";
        }
        if (count($recipient_ids)) {
            $where_groups[] = 'id IN(' . implode(',', $recipient_ids) . ')';
        }
        $query = "SELECT clang_id FROM " . rex::getTablePrefix() . "375_user " . "WHERE " . implode(" OR ", $where_groups) . " GROUP BY clang_id";

        $result = rex_sql::factory();
        $result->setQuery($query);
        $num_rows = $result->getRows();
        for ($i = 0; $num_rows > $i; $i++) {
            $clang_ids[] = $result->getValue('clang_id');
            $result->next();
        }

        // Read article
        foreach ($clang_ids as $clang_id) {
            $newsletter = MultinewsletterNewsletter::factory($article_id, $clang_id);

            if(!strlen($newsletter->htmlbody)) {
                $offline_lang_ids[] = $clang_id;
            }
            else {
                $newsletter->attachments = explode(",", $attachments);
                $newsletter->group_ids = $group_ids;
				$sender_email = rex_config::get('multinewsletter', 'sender');
				if(session_status() !== PHP_SESSION_NONE && isset($_SESSION['multinewsletter']) && isset($_SESSION['multinewsletter']['newsletter']) && isset($_SESSION['multinewsletter']['newsletter']['sender_email'])) {
					$sender_email = $_SESSION['multinewsletter']['newsletter']['sender_email'];
				}
                $newsletter->sender_email = $sender_email;
				$sender_email_name = rex_config::get('multinewsletter', 'lang_1_sendername');
				if(session_status() !== PHP_SESSION_NONE && isset($_SESSION['multinewsletter']) && isset($_SESSION['multinewsletter']['newsletter']) && isset($_SESSION['multinewsletter']['newsletter']['sender_name']) && isset($_SESSION['multinewsletter']['newsletter']['sender_name'][$clang_id])) {
					$sender_email_name = $_SESSION['multinewsletter']['newsletter']['sender_name'][$clang_id];
				}
                $newsletter->sender_name = $sender_email_name;
				$reply_to_email = rex_config::get('multinewsletter', 'reply_to');
				if(session_status() !== PHP_SESSION_NONE && isset($_SESSION['multinewsletter']) && isset($_SESSION['multinewsletter']['newsletter']) && isset($_SESSION['multinewsletter']['newsletter']['reply_to_email'])) {
					$reply_to_email = $_SESSION['multinewsletter']['newsletter']['reply_to_email'];
				}
                $newsletter->reply_to_email = $reply_to_email;
				$newsletter->sentby = rex::getUser() instanceof rex_user ? rex::getUser()->getLogin() : "MultiNewsletter Cronjob API Call";
                $newsletter->save();

                $this->archives[$newsletter->id] = $newsletter;
            }
        }

        // Add users to send list
        $where_offline_langs = [];
        foreach ($offline_lang_ids as $offline_lang_id) {
            $where_offline_langs[] = "clang_id = " . $offline_lang_id;
        }
        foreach ($this->archives as $archive_id => $newsletter) {
            $newsletter_lang_id = $newsletter->clang_id;

            if (!in_array($newsletter_lang_id, $offline_lang_ids)) {
                $query_add_users = "INSERT INTO `" . rex::getTablePrefix() . "375_sendlist` (`archive_id`, `user_id`, `autosend`) "
					. "SELECT ". $archive_id ." AS archive_id, `id`, ". ($this->autosend_only ? 1 : 0) ." AS autosend "
						. "FROM `" . rex::getTablePrefix() . "375_user` WHERE (" . implode(" OR ", $where_groups) . ") " . "AND (clang_id = " . $newsletter_lang_id;
                if ($newsletter_lang_id == $fallback_clang_id && count($where_offline_langs) > 0) {
                    $query_add_users .= " OR " . implode(" OR ", $where_offline_langs);
                }
                $query_add_users  .= ") AND status = 1 AND email != ''";
                $result_add_users = rex_sql::factory();
                $result_add_users->setQuery($query_add_users);
            }
        }

        return $offline_lang_ids;
    }

    /**
     * Setzt die zu versendenden Newsletter zurück. Dabei werden auch noch nicht
	 * versendete Archive gelöscht.
     */
    public function reset() {
        // Benutzer zurücksetzen
        $query_user  = "TRUNCATE " . rex::getTablePrefix() . "375_sendlist";
        $result_user = rex_sql::factory();
        $result_user->setQuery($query_user);
		$this->recipients = [];

        // Archive, die bisher keine Empfänger hatten auch löschen
        $query_archive  = "DELETE FROM " . rex::getTablePrefix() . "375_archive WHERE sentdate = 0";
        $result_archive = rex_sql::factory();
        $result_archive->setQuery($query_archive);
		$this->archives = [];

		$this->remaining_users = 0;
    }

    /**
     * Veranlasst das Senden der nächsten Trange von Mails.
     * @param int $numberMails Anzahl von Mails die raus sollen.
     * @return mixed TRUE, wenn erfolgreich versendet, otherwise array with
	 * failed email addresses.
     */
    public function send($numberMails) {
        if ($numberMails > $this->countRemainingUsers()) {
            $numberMails = $this->countRemainingUsers();
        }

	    $result = rex_sql::factory();
		$failure_mails = [];
		$success_mails = [];

        while ($numberMails > 0) {
            $recipient  = $this->recipients[$numberMails - 1];
			$archive_id = $recipient->getSendlistArchiveIDs($this->autosend_only);
            $newsletter = $this->archives[$archive_id[0]];

            if ($newsletter->sendNewsletter($recipient, rex_article::get($newsletter->article_id)) == FALSE) {
				$result->setQuery("DELETE FROM ". rex::getTablePrefix() ."375_sendlist WHERE user_id = ". $recipient->id ." AND archive_id = ". $newsletter->id);
                $failure_mails[] = $recipient->email;
            }

            // Delete user from sendlist
			$result->setQuery("DELETE FROM ". rex::getTablePrefix() ."375_sendlist WHERE user_id = ". $recipient->id ." AND archive_id = ". $newsletter->id);

            $this->last_send_users[] = $recipient;
			$success_mails[] = $recipient->email;
            $numberMails--;
        }

		if(count($failure_mails) == 0) {
			return TRUE;
		}
		else {
			return $failure_mails;
		}
    }
	
	/**
     * Sends a email to MutliNewsletter admin, if given in settings.
     * @param string $subject Message subject
     * @param string $body Message body
     * @return boolean TRUE if successfully sent, otherwise FALSE
     */
    private function sendAdminNotification($subject, $body) {
		$multinewsletter = rex_addon::get('multinewsletter');
		if($multinewsletter->getConfig('admin_email', '') != '') {
			$multinewsletter = rex_addon::get("multinewsletter");

			$mail = new rex_mailer();
			$mail->IsHTML(true);
			$mail->CharSet = "utf-8";
			$mail->From = $multinewsletter->getConfig('sender');
			$mail->FromName = "MultiNewsletter Manager";
			$mail->Sender = $multinewsletter->getConfig('sender');
			$mail->AddAddress($multinewsletter->getConfig('admin_email'));

			if ($multinewsletter->getConfig('use_smtp')) {
				$mail->Mailer = 'smtp';
				$mail->Host = $multinewsletter->getConfig('smtp_host');
				$mail->Port = $multinewsletter->getConfig('smtp_port');
				$mail->SMTPSecure = $multinewsletter->getConfig('smtp_crypt');
				$mail->SMTPAuth = $multinewsletter->getConfig('smtp_auth');
				$mail->Username = $multinewsletter->getConfig('smtp_user');
				$mail->Password = $multinewsletter->getConfig('smtp_password');
			}

			$mail->Subject = $subject;
			$mail->Body = $body;
			$success = $mail->Send();
			if(!$success) {
				print rex_view::error("Error sending admin notification: ". $mail->ErrorInfo);
			}
			return $success;
		}
    }
}