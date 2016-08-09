<?php

namespace App\Service;

use TwitterAPIExchange;


class TwitterService extends FeedsServiceAbstract
{
    private $username = 'rio2016';
    private $length = 20;

    public function __construct()
    {
        $settings = array(
            'oauth_access_token' => "44360731-kl6feNxIxrpr1pRDOJeBKXpu9eFxZ5Y7TJmHlOlEP",
            'oauth_access_token_secret' => "n3adIq8rSLOkMmsdMoFAxzzDc0MU6nQrwg0cfrTEyFucT",
            'consumer_key' => "Sxq6ksntPSmKCSGzzmxrmss8J",
            'consumer_secret' => "WFLKAj8YmS8A631r28gseqSvDHk8LeTJRELRl7fNcvn9iK7F5r"
        );

        $twitter = new TwitterAPIExchange($settings);

        $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
        $getfield = '?screen_name=' . $this->username . '&exclude_replies=true&include_rts=false&count=' . $this->length . '';
        $requestMethod = 'GET';

        $json = json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest());

        $ac = 0;
        foreach($json as $key => $item)
        {
            if(isset($item->entities->media))
            {
                $picture_data = array(
                    'name' => 'twitter_' . date('Ymd') . '.jpg',
                    'source' => str_replace('_normal.', '.', $item->user->profile_image_url),
                    'path' => __DIR__ . '/../../../data/pictures/'
                );

                if (!file_exists($picture_data['path'] . $picture_data['name']))
                {
                    file_put_contents($picture_data['path'] . $picture_data['name'], file_get_contents($picture_data['source']));
                }

                $image_data = array(
                    'name' => $item->id_str . '.jpg',
                    'source' => $item->entities->media[0]->media_url,
                    'path' => __DIR__ . '/../../../data/images/'
                );

                if (!file_exists($image_data['path'] . $image_data['name']))
                {
                    file_put_contents($image_data['path'] . $image_data['name'], file_get_contents($image_data['source']));
                }

                $text = preg_split("/\\r\\n|\\r|\\n/", $item->text);

                $this->addFeed(array(
                    'created' => date('Y-m-d H:i:s', strtotime($item->created_at)),
                    'typefeed' => 'twitter',
                    'user' => array(
                        'name' => $item->user->name,
                        'username' => $item->user->screen_name,
                    ),
                    'text' => $text,
                    'midia' => array(
                        'http://' . $_SERVER['HTTP_HOST'] . '/rio2016/data/pictures/' . $picture_data['name'],
                        'http://' . $_SERVER['HTTP_HOST'] . '/rio2016/data/images/' . $image_data['name']
                    )
                ));

                if($ac > 4)
                    break;

                $ac++;
            }
        }
    }
}