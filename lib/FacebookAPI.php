<?php

use Facebook\FacebookSession;
use Facebook\FacebookRequest;

class FacebookAPI
{
	use SimpleCacheTrait;

	/**
	 * These were created using the Aston Martin account Nick created.
	 */
	const APP_ID = '****************';
	const APP_SECRET = '********************************';

	private $session = null;

	public function __construct()
	{
		include dirname(__DIR__) . '/vendor/autoload.php';
		FacebookSession::setDefaultApplication(self::APP_ID, self::APP_SECRET);

		$resp = file_get_contents(
			"https://graph.facebook.com/oauth/access_token" .
			"?client_id=" . self::APP_ID .
			"&client_secret=" . self::APP_SECRET .
			"&grant_type=client_credentials"
		);

		if (strpos($resp, 'access_token=') !== 0) {
			throw new Exception("Facebook authorization failed.");
		}

		$token = preg_replace('/^access_token=/', '', $resp, 1);

		$this->session = new FacebookSession($token);
	}

	/**
	 * This is defaulting to AMNE.LM's id
	 */
	public function posts($user_id='************')
	{
		$request = new FacebookRequest($this->session, 'GET', "/$user_id/posts");
		$response = $request->execute();

		$graph_objects = $response->getGraphObjectList();

		$posts = array();
		foreach($graph_objects as $obj) {
			$post = $obj->asArray();

			if (isset($post['object_id'])) {
				$post['large_picture'] = 'https://graph.facebook.com/' . $post['object_id'] . '/picture';
			}
			$posts[] = $post;
		}

		return $posts;
	}
}
