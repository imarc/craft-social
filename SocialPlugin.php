<?php
/**
 * @copyright 2016 Imarc LLC
 * @author Kevin Hamer [kh] <kevin@imarc.com>
 * @license Apache (see LICENSE file)
 */

namespace Craft;

/**
 * SocialPlugin is a Craft plugin that provides Facebook, Twitter, Instragram,
 * and WordPress feed integration. It tries to aggregate and normalize posts to
 * these services so they can be easily displayed in a 'single stream' of mixed
 * content (by post date, for example.)
 */
class SocialPlugin extends BasePlugin
{
    public function init()
    {
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            require __DIR__ . '/vendor/autoload.php';
        }

        return parent::init();
    }

    public function getName()
    {
        return Craft::t('Social');
    }

    public function getVersion()
    {
        return '1.0';
    }

    public function getDeveloper()
    {
        return 'Imarc';
    }

    public function getDeveloperUrl()
    {
        return 'https://www.Imarc.com';
    }

    public function defineSettings()
    {
        return [
            'facebook_app_id' => [AttributeType::String, 'default' => ''],
            'facebook_app_secret' => [AttributeType::String, 'default' => ''],
            'facebook_user_id' => [AttributeType::String, 'default' => ''],

            'twitter_screen_name' => [AttributeType::String, 'default' => ''],
            'twitter_consumer_key' => [AttributeType::String, 'default' => ''],
            'twitter_consumer_secret' => [AttributeType::String, 'default' => ''],

            'wordpress_rss_feed' => [AttributeType::String, 'default' => ''],

            'instagram_access_token' => [AttributeType::String, 'default' => ''],
            'instagram_user_id' => [AttributeType::String, 'default' => ''],

            'social_cache_expiration' => [AttributeType::Number, 'default' => 1200],
        ];
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('social/settings', ['settings' => $this->getSettings()]);
    }
}
