<?php

/**
 * MultiNewsletter User Group.
 *
 * @author Tobias Krais
 */
class MultinewsletterGroup {
	/**
	 * @var int Database ID
	 */
	var $id = 0;
	
	/**
	 * @var string Name
	 */
	var $name = "";
	
	/**
	 * @var string Default sender email
	 */
	var $default_sender_email = "";
	
	/**
	 * @var string Default sender name
	 */
	var $default_sender_name = "";

	/**
	 * @var string Reply to email
	 */
	var $reply_to_email = "";
	
	/**
	 * @var int Default Redaxo article id
	 */
	var $default_article_id = 0;

	/**
	 * @var string Default Redaxo article name
	 */
	var $default_article_name = "";

	/**
	 * @var string Mailchimp list id
	 */
	var $mailchimp_list_id = "";

	/**
     * Fetch object data from database
     * @param int $id Group id from database
     */
    public function __construct($id) {
		$query = "SELECT * FROM ". \rex::getTablePrefix() ."375_group WHERE id = ". $id;
		$result = \rex_sql::factory();
		$result->setQuery($query);

		if ($result->getRows() > 0) {
			$this->id = $result->getValue("id");
			$this->name = $result->getValue("name");
			$this->default_sender_email = $result->getValue("default_sender_email");
			$this->default_sender_name = $result->getValue("default_sender_name");
			$this->reply_to_email = $result->getValue("reply_to_email");
			$this->default_article_id = $result->getValue("default_article_id");
			$default_article = rex_article::get($this->default_article_id);
			if($default_article instanceof rex_article) {
				$this->default_article_name = $default_article->getName();
			}
			$this->mailchimp_list_id = $result->getValue("mailchimp_list_id");
		}
    }

    /**
     * Deletes object in database.
     */
    public function delete() {
		$result = rex_sql::factory();
		$result->setQuery("DELETE FROM ". \rex::getTablePrefix() ."375_group WHERE id = ". $$this->id);
    }

    /**
     * Fetch all groups from database
     * @return MultinewsletterGroup[] Array containing all groups
     */
    public static function getAll() {
		$groups = [];
		$result = rex_sql::factory();
		$result->setQuery('SELECT id FROM '. rex::getTablePrefix() .'375_group ORDER BY name');

		for ($i = 0; $i < $result->getRows(); $i++) {
			$groups[$result->getValue('id')] = new MultinewsletterGroup($result->getValue('id'));
			$result->next();
		}
		return $groups;
    }
}