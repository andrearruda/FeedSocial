<?php

namespace App\Service;

use Facebook;

class FacebookService extends FeedsServiceAbstract
{
    private $username = 'rio2016pt';
    private $length = 5;
    private  $acessToken = 'EAAPfK6SRjpgBAEvFvUr4y9GjQZBJLVCvXO0h7hBtieZBmU383zKyB9qqNaf4svJA7lc2OKQwnZBT3FfTgTiPBZA8JV2TePFANKirfXJSnwVhBI4ZCvyHJtXwR9CTZA1ZBxwkPx8IR4uuMZAXKa4ufLzLhDrbzUoZCgZAMZD';

    public function __construct()
    {
        $fb = new Facebook\Facebook([
            'app_id' => '1089803467722392',
            'app_secret' => 'f40c536eca0b3db7a22c3fa8f373d873',
            'default_graph_version' => 'v2.7',
            'default_access_token' => $this->acessToken
        ]);

        $request = $fb->get('/' . $this->username . '?fields=feed.limit(' . $this->length . '){created_time, from{name, username, picture.width(500)}, type, message, description, attachments{media}, source}');
        $response = $request->getGraphPage()->getField('feed');

        foreach($response as $item)
        {
            $this->addFeed(array(
                'created' => $item->getField('created_time')->format('Y-m-d H:i:s'), #TODO acertar timezone
                'typefeed' => 'facebook',
                'user' => array(
                    'name' => $item->getField('from')->getField('name'),
                    'username' => $item->getField('from')->getField('username'),
                    'picture' => $item->getField('from')->getField('picture')->getField('url'),
                ),
                'text' => $item->getField('message'),
                'midia' => array(
                    'type' => $item->getField('type'),
                    'image' => $item->getField('attachments')->getField('0')->getField('media')->getField('image')->getField('src'),
                    'video' => $item->getField('type') == 'video' ? $item->getField('source') : ''
                ),
            ));
        }
    }
}