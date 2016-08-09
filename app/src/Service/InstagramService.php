<?php

namespace App\Service;

use Haridarshan\Instagram\Instagram;
use Haridarshan\Instagram\InstagramRequest;
use Haridarshan\Instagram\Exceptions\InstagramOAuthException;
use Haridarshan\Instagram\Exceptions\InstagramResponseException;
use Haridarshan\Instagram\Exceptions\InstagramServerException;

use Stringy\Stringy as S;

class InstagramService extends FeedsServiceAbstract
{
    private $username = 'rio2016';
    private $length = 5;

    public function __construct()
    {
        $json = json_decode(file_get_contents('https://www.instagram.com/' . $this->username . '/media/'));

        foreach($json->items as $key => $item)
        {
            if($key >= $this->length)
                break;

            $picture_data = array(
                'name' => 'instagram_' . date('Ymd') . '.jpg',
                'source' => $item->caption->from->profile_picture,
                'path' => __DIR__ . '/../../../data/pictures/'
            );

            if (!file_exists($picture_data['path'] . $picture_data['name']))
            {
                file_put_contents($picture_data['path'] . $picture_data['name'], file_get_contents($picture_data['source']));
            }

            $image_data = array(
                'name' => $item->id . '.jpg',
                'source' => $item->images->standard_resolution->url,
                'path' => __DIR__ . '/../../../data/images/'
            );

            if (!file_exists($image_data['path'] . $image_data['name']))
            {
                file_put_contents($image_data['path'] . $image_data['name'], file_get_contents($image_data['source']));
            }

            $text = array_shift(preg_split("/\\r\\n|\\r|\\n/", $item->caption->text));

            $this->addFeed(array(
                'created' => date('Y-m-d H:i:s', $item->caption->created_time),
                'typefeed' => 'instagram',
                'user' => array(
                    'name' => $item->caption->from->full_name,
                    'username' => $item->caption->from->username,
                ),
                'text' => (string) S::create($text)->safeTruncate(180, '...'),
                'midia' => array(
                    'http://' . $_SERVER['HTTP_HOST'] . '/rio2016/data/pictures/' . $picture_data['name'],
                    'http://' . $_SERVER['HTTP_HOST'] . '/rio2016/data/images/' . $image_data['name'],
                )
            ));
        }
    }
}