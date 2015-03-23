<?php
namespace Craft;

class SocialVariable
{
    const CACHE_FILE = 'posts.cache';

    public function cachePosts($posts=null)
    {
        $settings = craft()->plugins->getPlugin('social')->getSettings();

        $filename = craft()->path->getStoragePath() . 'social/';
        IOHelper::ensureFolderExists($filename);
        $filename = $filename . self::CACHE_FILE;

        if ($posts !== null) {
            file_put_contents($filename, json_encode($posts));
        } else {
            if (file_exists($filename)) {
                $age = time() - filemtime($filename);
                if ($age <= $settings->social_cache_expiration) {
                    return json_decode(file_get_contents($filename));
                }
            }
            return [];
        }
    }

    public function vendorPopulated()
    {
        return file_exists(dirname(__DIR__) . '/vendor/autoload.php');
    }

    public function posts(array $criteria=array())
    {
        $posts = $this->cachePosts();

        if (!$posts) {
            $posts = array_merge($posts, craft()->social_facebook->findPosts());
            $posts = array_merge($posts, craft()->social_twitter->findPosts());
            //$posts = array_merge($posts, craft()->social_instagram->findPosts());

            if ($posts) {
                $this->cachePosts($posts);
            }
        }

        if (count($criteria)) {
            foreach ($posts as $key => $post) {
                foreach ($criteria as $field => $req) {
                    if (!isset($post->{$field})) {
                        continue;
                    }
                    if (is_array($req)) {
                        if (!in_array($post->{$field}, $req)) {
                            unset($posts[$key]);
                        }
                    } elseif (is_string($req)) {
                        if ($post->{$field} != $req) {
                            unset($posts[$key]);
                        }
                    }
                }
            }
        }

        if (isset($criteria['limit'])) {
            $posts = array_slice($posts, 0, $criteria['limit']);
        }

        return $posts;
    }

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
}
