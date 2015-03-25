<?php
namespace Craft;

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
		return 'iMarc';
	}

	public function getDeveloperUrl()
	{
		return 'http://www.imarc.net';
	}

	function defineSettings()
	{
		return [
            'facebook_app_id'          => [AttributeType::String, 'default' => ''],
            'facebook_app_secret'      => [AttributeType::String, 'default' => ''],
            'facebook_user_id'         => [AttributeType::String, 'default' => ''],

            'twitter_screen_name'      => [AttributeType::String, 'default' => ''],
			'twitter_consumer_key'     => [AttributeType::String, 'default' => ''],
			'twitter_consumer_secret'  => [AttributeType::String, 'default' => ''],

            'wordpress_rss_feed'       => [AttributeType::String, 'defualt' => ''],

            'instagram_access_token'   => [AttributeType::String, 'default' => ''],

            'social_cache_expiration'  => [AttributeType::Number, 'default' => 1200]
		];
	}

	function getSettingsHtml()
	{
		return craft()->templates->render('social/settings', ['settings' => $this->getSettings()]);
	}
}
