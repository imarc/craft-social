<?php
namespace Craft;

class SocialVariable
{
	static public function relativeTime($timestamp, $newer_timestamp=NULL)
	{
		if ($newer_timestamp === NULL) {
			$newer_timestamp = time();
		}

		if (!is_numeric($timestamp)) {
			$timestamp = strtotime(preg_replace('/^[^ ]* /', '', $timestamp));
		}

		$time_since = $newer_timestamp - $timestamp;

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

	public function twitterUserTimeline($params)
	{
		return craft()->social_twitter->userTimeline($params);
	}

	public function facebookPosts()
	{
		return craft()->social_facebook->posts();
	}

	public function instagramPosts()
	{
		return craft()->social_instagram->posts();
	}

	static private $facebook_posts = null;
	public function facebookNext()
	{
		if (null === self::$facebook_posts) {
			self::$facebook_posts = $this->facebookPosts();
		}

		$post = array_shift(self::$facebook_posts);
		$post['relative_time'] = self::relativeTime($post['created_time']);

		if (!isset($post['message']) && !isset($post['large_picture'])) {
			return $this->facebookNext();
		}

		return $post;
	}

	static private $instagram_posts = null;
	public function instagramNext()
	{
		if (null === self::$instagram_posts) {
			self::$instagram_posts = $this->instagramPosts();
		}

		$post = array_shift(self::$instagram_posts);

		$post['relative_time'] = self::relativeTime($post['created_time']);
		return $post;
	}

	static private $twitter_posts = null;
	public function twitterNext()
	{
		if (null === self::$twitter_posts) {
			self::$twitter_posts = $this->twitterUserTimeline('astonmartin');
		}

		return array_shift(self::$twitter_posts);
	}
}
