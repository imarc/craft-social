<?php
namespace Craft;

class Social_TwitterService extends BaseApplicationComponent
{
    const TOKEN_URL             = 'https://api.twitter.com/oauth2/token';
    const USER_TIMELINE_URL     = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
    const MENTIONS_TIMELINE_URL = 'https://api.twitter.com/1.1/statuses/mentions_timeline.json';
    const HOME_TIMELINE_URL     = 'https://api.twitter.com/1.1/statuses/home_timeline.json';
    const RETWEETS_TIMELINE_URL = 'https://api.twitter.com/1.1/statuses/retweets_of_me.json';

    private $token = null;

    protected function getBearerToken()
    {
        $settings = craft()->plugins->getPlugin('social')->getSettings();

        if (!$settings->twitter_consumer_key || !$settings->twitter_consumer_secret) {
            return false;
        }

        if ($this->token) {
            return $this->token;
        }

        $store = craft()->path->getStoragePath() . 'social/';
        IOHelper::ensureFolderExists($store);

        if (file_exists($store . '/twitter.bearer-token')) {
            $this->token = trim(file_get_contents($store . '/twitter.bearer-token'));
        } else {
            $curl = curl_init(self::TOKEN_URL);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt(
                $curl,
                CURLOPT_USERPWD,
                $settings->twitter_consumer_key . ':' . $settings->twitter_consumer_secret
            );

            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array(
                'grant_type' => 'client_credentials'
            )));

            $response = curl_exec($curl);
            $status   = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($status == 200) {
                $decoded = json_decode($response);
                $this->token = $decoded->access_token;

            } else {
                return false;
            }

            file_put_contents($store . '/twitter.bearer-token', $this->token);
        }

        return $this->token;
    }

    protected function request($url, $params)
    {
        $url_with_params = $url . '?' . http_build_query($params);

        $token = $this->getBearerToken();

        $context = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => join("\r\n", array("Authorization: Bearer " . $token)),
                'ignore_errors' => TRUE
            )
        ));

       $response = file_get_contents(
            $url_with_params, FALSE, $context
        );

        return json_decode($response);
    }

    public function findPosts($options=array())
    {
        $settings = craft()->plugins->getPlugin('social')->getSettings();

        $defaults = array(
            'screen_name' => $settings->twitter_screen_name
        );
        $options = array_merge($defaults, $options);

        $tweets = $this->request(self::USER_TIMELINE_URL, $options);

        $posts = array();

        $t = 'https://www.twitter.com/';

        foreach ($tweets as $tweet) {
            if (!is_object($tweet)) {
                continue;
            }

            $picture = false;
            if (isset($tweet->entities->media) && count($tweet->entities->media)) {
                $picture = $tweet->entities->media[0]->media_url;
            }

            // remove the 'trailing' t.co link
            $message = preg_replace('#http://t.co/[^ ]*$#', '', $tweet->text);

            // linkify remaining URLs
            $message = preg_replace(
                '/(https?:\/\/([A-Za-z0-9-._~:\/?#\[\]@!$&\'()*+,;=%]*))/',
                '<a href="\1">\2</a>',
                $message
            );


            $posts[] = array(
                'network' => 'Twitter',
                'message' => $message,
                'link'    => $t . $tweet->user->name . '/status/' . $tweet->id_str,

                'picture' => $picture,
                'author'  => $tweet->user->name,
                'author_link' => $tweet->user->url,
                'created' => strtotime($tweet->created_at),

                'print_r' => print_r($tweet, true),
                'native'  => $tweet
            );
        }

        return $posts;
    }
}
