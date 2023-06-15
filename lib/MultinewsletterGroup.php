<?php

/**
 * MultiNewsletter User Group.
 *
 * @author Tobias Krais
 */
class MultinewsletterGroup
{
    /** @var int Database ID */
    public int $id = 0;

    /** @var string Name */
    public string $name = '';

    /** @var string Default sender email */
    public string $default_sender_email = '';

    /** @var string Default sender name */
    public string $default_sender_name = '';

    /** @var string Reply to email */
    public string $reply_to_email = '';

    /** @var int Default Redaxo article id */
    public int $default_article_id = 0;

    /** @var string Default Redaxo article name */
    public string $default_article_name = '';

    /** @var string Mailchimp list id */
    public string $mailchimp_list_id = '';

    /**
     * Fetch object data from database.
     * @param int $id Group id from database
     */
    public function __construct($id)
    {
        $query = 'SELECT * FROM '. \rex::getTablePrefix() .'375_group WHERE id = '. $id;
        $result = \rex_sql::factory();
        $result->setQuery($query);

        if ($result->getRows() > 0) {
            $this->id = (int) $result->getValue('id');
            $this->name = (string) $result->getValue('name');
            $this->default_sender_email = (string) $result->getValue('default_sender_email');
            $this->default_sender_name = (string) $result->getValue('default_sender_name');
            $this->reply_to_email = (string) $result->getValue('reply_to_email');
            $this->default_article_id = (int) $result->getValue('default_article_id');
            $default_article = rex_article::get($this->default_article_id);
            if ($default_article instanceof rex_article) {
                $this->default_article_name = $default_article->getName();
            }
            $this->mailchimp_list_id = (string) $result->getValue('mailchimp_list_id');
        }
    }

    /**
     * Deletes object in database.
     */
    public function delete(): void
    {
        $result = rex_sql::factory();
        $result->setQuery('DELETE FROM '. \rex::getTablePrefix() .'375_group WHERE id = '. $$this->id);
    }

    /**
     * Fetch all groups from database.
     * @return MultinewsletterGroup[] Array containing all groups
     */
    public static function getAll()
    {
        $groups = [];
        $result = rex_sql::factory();
        $result->setQuery('SELECT id FROM '. rex::getTablePrefix() .'375_group ORDER BY name');

        for ($i = 0; $i < $result->getRows(); ++$i) {
            $groups[$result->getValue('id')] = new self($result->getValue('id'));
            $result->next();
        }
        return $groups;
    }
}
