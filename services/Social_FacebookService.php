<?php
namespace Craft;

use Exception;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;

class Social_FacebookService extends BaseApplicationComponent
{
    private $session = null;

    private function getFacebookSession()
    {
        $settings = craft()->plugins->getPlugin('social')->getSettings();

        if (!$settings->facebook_app_id || !$settings->facebook_app_secret) {
            return false;
        }

        FacebookSession::setDefaultApplication(
            $settings->facebook_app_id,
            $settings->facebook_app_secret
        );

        $response = file_get_contents(
            'https://graph.facebook.com/oauth/access_token?'
            . http_build_query(array(
                'client_id' => $settings->facebook_app_id,
                'client_secret' => $settings->facebook_app_secret,
                'grant_type' => 'client_credentials'
            ))
        );

        if (strpos($response, 'access_token')) {
        	$response = json_decode($response);
	        $this->session = new FacebookSession($response->access_token);
        } else {
            SocialPlugin::log('Facebook authentication failed.', LogLevel::Error);
            $this->session = false;
	    }

        return $this->session;
    }

    public function findPosts()
    {
        $settings = craft()->plugins->getPlugin('social')->getSettings();
        $user_id = $settings->facebook_user_id;

        $session = $this->getFacebookSession();

        if ($session === false) {
            return array();
        }

        $request = new FacebookRequest(
            $session,
            'GET',
            "/$user_id/posts"
        );
        $response = $request->execute();

        $graph_objects = $response->getGraphObjectList();

        $posts = array();

        $f = 'http://www.facebook.com/';

        foreach ($graph_objects as $graph_object) {
            $native = $graph_object->asArray();

            // remove the 'trailing' t.co link
            if (isset($native['message'])) {
                $message = preg_replace('#http://t.co/[^ ]*$#', '', $native['message']);
            } else {
                $message = null;
            }

            // linkify remaining URLs
            $message = preg_replace(
                '/(https?:\/\/([A-Za-z0-9-._~:\/?#\[\]@!$&\'()*+,;=%]*))/',
                '<a href="\1">\2</a>',
                $message
            );

            $posts[] = array(
                'network'     => 'Facebook',
                'message'     => $message,
                'link'        => isset($native['link']) ? $native['link'] : ($f . $native['id']),
                'picture'     => isset($native['picture']) ? $native['picture'] : false,
                'author'      => isset($native['from']) ? $native['from']->name : false,
                'author_link' => isset($native['from']) ? $f . $native['from']->id : false,
                'created'     => strtotime($native['created_time']),

                'print_r'     => print_r($native, true),
                'native'      => $native
            );
        }

        return $posts;
    }
}
