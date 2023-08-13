<?php
/**
 * MultiNewsletters user list object.
 */
class MultinewsletterUserList
{
    /** @var MultinewsletterUser[] array mit Benutzerobjekten */
    public $users = [];

    /**
     * Stellt die Daten des Benutzers aus der Datenbank zusammen.
     * @param MultinewsletterUser[] $user_ids array mit UserIds aus der Datenbank
     */
    public function __construct($user_ids)
    {
        foreach ($user_ids as $id) {
            $this->users[] = new MultinewsletterUser($id);
        }
    }

    /**
     * Exportiert die Benutzerliste als CSV und sendet das Dokument als CSV.
     */
    public static function countAll()
    {
        $query = 'SELECT COUNT(*) as total FROM ' . rex::getTablePrefix() . "375_user WHERE email != ''";
        $result = rex_sql::factory();
        $result->setQuery($query);

        return $result->getValue('total');
    }

    /**
     * Get all users.
     * @param bool $ignoreInactive only online news
     * @param int $clang_id redaxo clang id
     * @return News[] array with User objects
     */
    public static function getAll($ignoreInactive = true, $clang_id = null)
    {
        $users = [];
        $sql = rex_sql::factory();
        $filter = ['1'];
        $params = [];

        if ($ignoreInactive) {
            $filter[] = 'email != ""';
            $filter[] = '`status` = 1';
        }
        if ($clang_id > 0) {
            $where[] = 'clang_id = :clang_id';
            $params['clang_id'] = $clang_id;
        }
        $query = 'SELECT id FROM ' . rex::getTablePrefix() . '375_user WHERE ' . implode(' AND ', $filter) . ' ORDER BY firstname, lastname';
        $sql->setQuery($query, $params);
        $num_rows = $sql->getRows();

        for ($i = 0; $i < $num_rows; ++$i) {
            $users[] = new MultinewsletterUser($sql->getValue('id'));
            $sql->next();
        }
        return $users;
    }

    /**
     * Exportiert die Benutzerliste als CSV und sendet das Dokument als CSV.
     */
    public function exportCSV()
    {
        if (count($this->users)) {
            $cols = [
                'id',
                'email',
                'grad',
                'firstname',
                'lastname',
                'title',
                'clang_id',
                'status',
                'group_ids',
                'mailchimp_id',
                'createdate',
                'createip',
                'activationdate',
                'activationip',
                'activationkey',
                'updatedate',
                'updateip',
                'subscriptiontype',
                'privacy_policy_accepted',
            ];
            $lines = [implode(';', $cols)];

            foreach ($this->users as $user) {
                $line = [
                    $user->id,
                    $user->email,
                    $user->grad,
                    $user->firstname,
                    $user->lastname,
                    $user->title,
                    $user->clang_id,
                    $user->status,
                    implode('|', $user->group_ids),
                    $user->mailchimp_id,
                    $user->createdate,
                    $user->createip,
                    $user->activationdate,
                    $user->activationip,
                    $user->activationkey,
                    $user->updatedate,
                    $user->updateip,
                    $user->subscriptiontype,
                    $user->privacy_policy_accepted,
                ];
                $lines[] = implode(';', $line);
            }

            $content = implode("\n", $lines);

            header('Cache-Control: public');
            header('Content-Description: File Transfer');
            header('Content-disposition: attachment; filename=multinewsletter_user.csv');
            header('Content-Type: application/csv');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . strlen($content));
            echo $content;
            exit;
        }
    }
}
