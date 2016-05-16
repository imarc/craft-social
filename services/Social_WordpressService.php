<?php
namespace Craft;

class Social_WordpressService extends BaseApplicationComponent
{
    public function findPosts()
    {
        $settings = craft()->plugins->getPlugin('social')->getSettings();

        if (!$settings->wordpress_rss_feed) {
            return array();
        }

        $response = simplexml_load_file($settings->wordpress_rss_feed);

        $posts = array();

        foreach ($response->channel->item as $item) {
            $post = array(
                'network'     => 'WordPress',
                'title'       => (string) $item->title,
                'message'     => (string) $item->description,
                'link'        => (string) $item->link,
                'picture'     => null,
                'author'      => (string) $item->{'dc:creator'},
                'author_link' => null,
                'created'     => strtotime((string) $item->pubDate),

                'native'      => (array) $item
            );

            $post['print_r'] = print_r($post, true);
            $posts[] = $post;
        }

        return $posts;
    }
}
