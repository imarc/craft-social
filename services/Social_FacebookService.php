<?php
namespace Craft;

use FacebookAPI;

class Social_FacebookService extends BaseApplicationComponent
{
	protected $api_client = null;

	public function getAPIClient()
	{
		if ($this->api_client === null) {
			trait_exists('SimpleCacheTrait') || include dirname(__DIR__) . '/lib/SimpleCacheTrait.php';
			class_exists('FacebookAPI') || include dirname(__DIR__) . '/lib/FacebookAPI.php';

			$this->api_client = new FacebookAPI();
		}

		return $this->api_client;
	}

	public function posts()
	{
		$client = $this->getAPIClient();

		return $client->posts();
	}
}
