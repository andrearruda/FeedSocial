<?php

namespace App\Service;

class InstagramService extends FeedsServiceAbstract
{
    private $username = 'rio2016';

    public function __construct()
    {
        $json = json_decode(file_get_contents('https://www.instagram.com/' . $this->username . '/media/'));

        foreach($json->items as $key => $item)
        {
            $this->addFeed(array(
                'created' => date('Y-m-d H:i:s', $item->caption->created_time),
                'typefeed' => 'instagram',
                'user' => array(
                    'name' => $item->caption->from->full_name,
                    'username' => $item->caption->from->username,
                    'picture' => $item->caption->from->profile_picture,
                ),
                'text' => $item->caption->text,
                'midia' => $item->type == 'video' ? $item->videos->standard_resolution->url : $item->images->standard_resolution->url,
            ));
        }
    }
}