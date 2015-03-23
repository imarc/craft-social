<?php
namespace Craft;

class SocialPlugin extends BasePlugin
{
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
			'twitter_consumer_key'     => [AttributeType::String, 'default' => ''],
			'twitter_consumer_secret'  => [AttributeType::String, 'default' => ''],
			'twitter_cache_expiration' => [AttributeType::Number, 'default' => 1200]
		];
	}

	function getSettingsHtml()
	{
		return craft()->templates->render('social/settings', ['settings' => $this->getSettings()]);
	}
}
