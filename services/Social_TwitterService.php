<?php
namespace Craft;

use TwitterAPI;

class Social_TwitterService extends BaseApplicationComponent
{
	protected $api_client = null;

	protected function getAPIClient()
	{
		if ($this->api_client === null) {

			trait_exists('SimpleCacheTrait') || include dirname(__DIR__) . '/lib/SimpleCacheTrait.php';
			class_exists('TwitterAPI') || include dirname(__DIR__) . '/lib/TwitterAPI.php';

			defined('TWITTER_BEARER_TOKEN') || define('TWITTER_BEARER_TOKEN', '******************************************************************************************************************');

			$settings = craft()->plugins->getPlugin('social')->getSettings();

			$this->api_client = new TwitterAPI(
				$settings->twitter_consumer_key,
				$settings->twitter_consumer_secret,
				$settings->twitter_cache_expiration
			);
		}

		return $this->api_client;
	}

	public function userTimeline($params)
	{
		if (is_string($params)) {
			$params = array(
				'screen_name' => $params,
				'exclude_replies' => true
			);
		}

		return $this->getAPIClient()->userTimeline($params);
	}
}
