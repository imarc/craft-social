<?php
/**
 * Provides basic integration with the Twitter v1.1 API.
 *
 * Depends on APC (for caching, and PHP >= 5.3.0 (for json_encode and json_decode).
 *
 * @author Kevin Hamer <kevin@imarc.net> [kh, 2013-06-18]
 * @author Jeff Turcotte <jeff@imarc.net> [jt, 2013-07-01]
 *
 * @version 3.0
 *
 * @changes 3.0  Rewrite to use Memcached and such [kh, 2014-11-18]
 * @changes 2.0  Removed named instances, added constructor [jt, 2013-07-01]
 * @changes 1.0  Works [kh, 2013-06-18]
 */
class TwitterAPI
{
	use SimpleCacheTrait;

	const TOKEN_URL             = 'https://api.twitter.com/oauth2/token';
	const USER_TIMELINE_URL     = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
	const MENTIONS_TIMELINE_URL = 'https://api.twitter.com/1.1/statuses/mentions_timeline.json';
	const HOME_TIMELINE_URL     = 'https://api.twitter.com/1.1/statuses/home_timeline.json';
	const RETWEETS_TIMELINE_URL = 'https://api.twitter.com/1.1/statuses/retweets_of_me.json';

	/**
	 * Returns a Twitter-like, relative description of the difference in time since
	 * $timestamp. Optionally, you can specify 'now'.
	 *
	 * $timestamp        The historical timestamp you'd like to describe.
	 * $newer_timestamp  Defaults to now.
	 */
	static public function relativeTime($timestamp, $newer_timestamp=NULL)
	{
		if ($newer_timestamp === NULL) {
			$newer_timestamp = time();
		}

		$time_since = $newer_timestamp - strtotime(preg_replace('/^[^ ]* /', '', $timestamp));

		if ($time_since > 604800) {
			$weeks = floor($time_since/604800);
			return $weeks . ' ' . ($weeks == 1 ? 'week' : 'weeks') . ' ago';
		} elseif ($time_since > 86400) {
			$days = floor($time_since/86400);
			return $days . ' ' . ($days == 1 ? 'day' : 'days') . ' ago';
		} elseif ($time_since > 3600) {
			$hours = floor($time_since/3600);
			return $hours . ' ' . ($hours == 1 ? 'hour' : 'hours') . ' ago';
		} elseif ($time_since > 60) {
			$minutes = floor($time_since/60);
			return $minutes . ' ' . ($minutes == 1 ? 'minute' : 'minutes') . ' ago';
		} else {
			$seconds = $time_since;
			return $seconds . ' ' . ($seconds == 1 ? 'second' : 'seconds') . ' ago';
		}
	}

	private $consumer_key = NULL;
	private $consumer_secret = NULL;
	private $content_expiration = NULL;

	/**
	 * Construct/Configure the Twitter API
	 *
	 * @param string consumer_key  The Twitter API consumer key
	 * @param string consumer_secret  The Twitter API consumer secret
	 * @param integer content_expiration  Seconds to cache the response from the Twitter API
	 */
	public function __construct($consumer_key, $consumer_secret, $content_expiration=300)
	{
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_secret;
		$this->content_expiration = $content_expiration;
	}

	/**
	 * This is step one of OAuth integration - it fetches a new bearer token from Twitter.
	 *
	 * This is actually unnnecessary to do for the most part, as a bearer token can be kept around
	 * more or less idefinitely. See constants.php for TWITTER_BEARER_TOKEN for what we're using
	 * now.
	 */
	/* NOT IN USE, but please do not delete.
	private function getBearerToken()
	{
		$context = stream_context_create(array('http' => array(
			'method' => 'POST',
			'header' => join("\r\n", array(
				'Authorization: Basic ' . base64_encode($this->consumer_key . ":" . $this->consumer_secret),
				'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
			)),
			'content' => 'grant_type=client_credentials',
			'ignore_errors' => TRUE
		)));

		$token_url = self::TOKEN_URL;

		$response = $this->suppressErrors(function() use ($token_url, $context) {
			return json_decode(file_get_contents(
				$token_url, FALSE, $context
			));
		});

		if (isset($response->access_token)) {
			return $response->access_token;
		}
	}
	*/

	/**
	 * Gets a new bearer token and does an HTTP GET to $url with $params.
	 *
	 * It handles caching in APC as well.
	 *
	 * $url     string  URL to do an HTTP GET to.
	 * $params  array   URL Parameters, passed to http_build_query().
	 */
	protected function request($url, $params)
	{
		$url_with_params = $url . '?' . http_build_query($params);
		$response        = $this->cache($url_with_params);

		if ($response) {
			return $response;
		}

		$context = stream_context_create(array(
			'http' => array(
				'method' => 'GET',
				'header' => join("\r\n", array("Authorization: Bearer " . TWITTER_BEARER_TOKEN)),
				'ignore_errors' => TRUE
			)
		));

		$response = $this->suppressErrors(function() use ($url_with_params, $context) {
			return json_decode(file_get_contents(
				$url_with_params, FALSE, $context
			));
		});

		if ($response) {
			return $this->cache($url_with_params, $response);
		}
	}


	/**
	 * Most recent tweets posted by a specific screen_name.
	 */
	public function userTimeline($params)
	{
		if (!isset($params['screen_name'])) {
			throw new InvalidArgumentException(
				"User timeline requests require 'screen_name'."
			);
		}

		$screen_name = $params['screen_name'];
		$tweets = $this->request(self::USER_TIMELINE_URL, $params);

		// An object gets returned if an error occured
		if (!$tweets || is_object($tweets)) {
			return array();
		}

		foreach ($tweets as $key => $tweet) {
			if (!is_object($tweet)) {
				unset($tweets[$key]);
				continue;
			}
			$tweet->link = "https://twitter.com/$screen_name/status/" . $tweet->id_str;
			$tweet->relative = self::relativeTime($tweet->created_at);

			$tweet->autolinkedText = preg_replace('#(https?://([^ ]*))#', '<a href="\1">\2</a>', $tweet->text);
		}

		return $tweets;
	}

	/**
	 * Suppress Errors
	 */
	protected function suppressErrors($callback) {
		return $callback();
	}
}
