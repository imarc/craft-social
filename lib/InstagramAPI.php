<?php
class InstagramAPI
{
	/**
	 * The Instagram User ID for is 1410669 (via http://jelled.com/instagram/lookup-user-id)
	 * The Instagram Client ID is 4efee33fe0e646038a59b1cd303753bb
	 */
	const TOKEN = '***************************************************';

	const RECENT_MEDIA = 'https://api.instagram.com/v1/users/*******/media/recent/';

	public function posts()
	{
		$resp = file_get_contents(self::RECENT_MEDIA . '?access_token=' . self::TOKEN);

		$data = json_decode($resp, true);

		if (!$data['data']) {
			throw new Exception("Instagram problem");
		}

		//return print_r($data['data'], true);
		return $data['data'];
	}
}
