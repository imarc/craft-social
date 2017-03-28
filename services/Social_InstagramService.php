<?php
namespace Craft;

use InstagramAPI;

class Social_InstagramService extends BaseApplicationComponent
{
    const RECENT_MEDIA = 'https://api.instagram.com/v1/users/';

    public function findPosts()
    {
        $settings = craft()->plugins->getPlugin('social')->getSettings();

        if (!$settings->instagram_access_token) {
            return array();
        }

        if (!$settings->instagram_user_id) {
            return array();
        }

        $url = static::RECENT_MEDIA . $settings->instagram_user_id . '/media/recent/';
        $url .= '?access_token=' . $settings->instagram_access_token;

        $response = file_get_contents($url);

        $data = json_decode($response, true);

        if (!isset($data['data'])) {
        	SocialPlugin::log('Error communicating with the Instagram API', LogLevel::Error);
        	$posts = array();
        } else {
	        $posts = array();
	        foreach ($data['data'] as $post) {
	            $author_link = 'https://www.instagram.com/' . $post['user']['username'];
	            $posts[] = array(
	                'network' => 'Instagram',
	                'message' => $post['caption']['text'],
	                'link'    => $post['link'],
	                'picture' => $post['images']['standard_resolution']['url'],
	                'author'  => $post['user']['username'],
	                'author_link' => $author_link,
	                'created' => $post['created_time'],
	                'native' => $post,
	                'print_r' => print_r($post, true)
	            );
	        }
	    }

        return $posts;
    }
}
