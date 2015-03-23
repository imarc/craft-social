<?php
namespace Craft;

use InstagramAPI;

class Social_InstagramService extends BaseApplicationComponent
{
	protected $api_client = null;

	protected function getAPIClient()
	{
		if ($this->api_client === null) {

			trait_exists('SimpleCacheTrait') || include dirname(__DIR__) . '/lib/SimpleCacheTrait.php';
			class_exists('InstagramAPI') || include dirname(__DIR__) . '/lib/InstagramAPI.php';

			$this->api_client = new InstagramAPI();
		}

		return $this->api_client;
	}

	public function posts()
	{
		$client = $this->getAPIClient();

		return $client->posts();
	}
}
