<?php
namespace Craft;

/**
 * This still needs some bug fixing. In particular, there's differences between
 * the 'cached' posts and the live thing.
 */
class SocialVariable
{
    public static function relativeTime($timestamp, $newer_timestamp = null, $format = "natural")
    {
        if ($newer_timestamp === null) {
            $newer_timestamp = time();
        }

        if (!is_numeric($timestamp)) {
            $timestamp = strtotime(preg_replace('/^[^ ]* /', '', $timestamp));
        }

        $time_since = $newer_timestamp - $timestamp;

        if ($time_since > 604800) {
            $weeks = floor($time_since / 604800);
            if ($format == 'dense') {
                return $weeks . 'w';
            } else {
                return $weeks . ' ' . ($weeks == 1 ? 'week' : 'weeks') . ' ago';
            }
        } elseif ($time_since > 86400) {
            $days = floor($time_since / 86400);
            if ($format == 'dense') {
                return $days . 'd';
            } else {
                return $days . ' ' . ($days == 1 ? 'day' : 'days') . ' ago';
            }
        } elseif ($time_since > 3600) {
            $hours = floor($time_since / 3600);
            if ($format == 'dense') {
                return $hours . 'h';
            } else {
                return $hours . ' ' . ($hours == 1 ? 'hour' : 'hours') . ' ago';
            }
        } elseif ($time_since > 60) {
            $minutes = floor($time_since / 60);
            if ($format == 'dense') {
                return $minutes . 'm';
            } else {
                return $minutes . ' ' . ($minutes == 1 ? 'minute' : 'minutes') . ' ago';
            }
        } else {
            $seconds = $time_since;
            if ($format == 'dense') {
                return $seconds . 's';
            } else {
                return $seconds . ' ' . ($seconds == 1 ? 'second' : 'seconds') . ' ago';
            }
        }
    }

    static $networks = null;

    public function __construct()
    {
        if (self::$networks === null) {
            self::$networks = [
                'Facebook' => craft()->social_facebook,
                'Instagram' => craft()->social_instagram,
                'Twitter' => craft()->social_twitter,
                'WordPress' => craft()->social_wordpress,
            ];
        }
    }

    public function cache($network, $posts = null)
    {
        $settings = craft()->plugins->getPlugin('social')->getSettings();

        $filename = craft()->path->getStoragePath() . 'social/';
        IOHelper::ensureFolderExists($filename);
        $filename = $filename . $network . ".posts";

        if ($posts !== null) {
            file_put_contents($filename, json_encode($posts));
        } else {
            if (file_exists($filename)) {
                $age = time() - filemtime($filename);
                if ($age <= $settings->social_cache_expiration) {
                    return json_decode(file_get_contents($filename), true);
                }
            }
            return array();
        }
    }

    public function vendorPopulated()
    {
        return file_exists(CRAFT_BASE_PATH . '../vendor/autoload.php');
    }

    public function posts(array $criteria = array())
    {
        $posts = array();

        if (isset($criteria['network'])) {
            if (is_string($criteria['network'])) {
                $networks = array($criteria['network']);
            } else {
                $networks = $criteria['network'];
            }
        } else {
            $networks = array_keys(self::$networks);
        }

        foreach ($networks as $network) {
            $service = self::$networks[$network];

            $network_posts = $this->cache($network);
            if (!$network_posts || isset($criteria['no_cache'])) {
                $network_posts = $service->findPosts($criteria);
                $this->cache($network, $network_posts);
            }

            $posts = array_merge($posts, $network_posts);
        }

        usort($posts, function ($a, $b) {
            return $a['created'] < $b['created'];
        });

        if (isset($criteria['limit'])) {
            $posts = array_slice($posts, 0, $criteria['limit']);
        }

        foreach ($posts as &$post) {
            $post['relative'] = static::relativeTime($post['created']);
            $post['rel'] = static::relativeTime($post['created'], null, 'dense');
        }

        return $posts;
    }
}
