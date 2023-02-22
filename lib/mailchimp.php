<?php

/**
 * This file is part of the Kreatif\Project package.
 *
 * @author Kreatif GmbH
 * @author a.platter@kreatif.it
 * Date: 04.09.17
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class MultinewsletterMailchimp
{
    private static $inst;
    private static $api_key = '';
    private static $data_center = '';

    private function __construct() {}

    public static function factory()
    {
        if (null === self::$inst) {
            self::$inst = new self();
            self::$api_key = rex_addon::get('multinewsletter')->getConfig('mailchimp_api_key');
            [$a, self::$data_center] = explode('-', self::$api_key);
        }
        return self::$inst;
    }

    public static function isActive()
    {
        return strlen(self::$api_key) || strlen(rex_addon::get('multinewsletter')->getConfig('mailchimp_api_key'));
    }

    public function getLists()
    {
        $result = $this->request('/lists');
        return $result['lists'];
    }

    public function addUserToList(MultinewsletterUser $user, $listId, $status = 'pending')
    {
        $hash = md5($user->email);

        // check user is not already signed
        try {
            $this->request("/lists/{$listId}/members/{$hash}");
            $result = $this->request("/lists/{$listId}/members/{$hash}", 'PATCH', ['status' => $status]);
        } catch (MultinewsletterMailchimpException $ex) {
            $result = $this->request("/lists/{$listId}/members/", 'POST', [
                'email_address' => $user->email,
                'status' => $status,
                'merge_fields' => [
                    'FNAME' => $user->firstname,
                    'LNAME' => $user->lastname,
                ],
            ]);
        }
        return $result;
    }

    public function unsubscribe(MultinewsletterUser $User, $listId)
    {
        $hash = md5($User->email);

        return $this->request("/lists/{$listId}/members/{$hash}", 'POST', [
            'status' => 'unsubscribed',
        ]);
    }

    public function request($path, $type = 'GET', $fields = [])
    {
        // open connection
        $ch = curl_init();
        $url = 'https://' . self::$data_center . '.api.mailchimp.com/3.0' . $path;
        //        pr($url);

        // set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, HTTP_AUTH_BASIC, true);
        curl_setopt($ch, CURLOPT_USERPWD, 'anystring:' . self::$api_key);

        if ('GET' != $type) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "X-HTTP-Method-Override: {$type}",
            ]);
        }
        // execute post
        $result = curl_exec($ch);
        // close connection
        curl_close($ch);

        $decoded_json = (array) json_decode($result, true);

        if (JSON_ERROR_NONE == json_last_error()) {
            $result = $decoded_json;
        } else {
            throw new MultinewsletterMailchimpException('Mailchimp: Request Not Found', 1);
        }
        if ('404' == $result['status']) {
            throw new MultinewsletterMailchimpException('Mailchimp: ' . $result['detail'], 2);
        }
        return $result;
    }
}

class MultinewsletterMailchimpException extends Exception
{
}
