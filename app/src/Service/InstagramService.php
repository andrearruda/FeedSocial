<?php

namespace App\Service;

use Haridarshan\Instagram\Instagram;
use Haridarshan\Instagram\InstagramRequest;
use Haridarshan\Instagram\Exceptions\InstagramOAuthException;
use Haridarshan\Instagram\Exceptions\InstagramResponseException;
use Haridarshan\Instagram\Exceptions\InstagramServerException;

class InstagramService extends FeedsServiceAbstract
{
    private $username = 'rio2016';
    private $length = 5;

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
                'midia' => array(
                    'type' => $item->type,
                    'image' => $item->images->standard_resolution->url,
                    'video' => $item->type == 'video' ? $item->videos->standard_resolution->url : ''
                ),
            ));

            if($key >= $this->length)
                break;
        }
    }
}