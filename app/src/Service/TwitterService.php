<?php

namespace App\Service;

use TwitterAPIExchange;


class TwitterService extends FeedsServiceAbstract
{
    private $username = 'rio2016';
    private $length = 10;

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

        foreach($json as $key => $item)
        {
            $this->addFeed(array(
                'created' => date('Y-m-d H:i:s', strtotime($item->created_at)),
                'typefeed' => 'twitter',
                'user' => array(
                    'name' => $item->user->name,
                    'username' => $item->user->screen_name,
                    'picture' => str_replace('_normal.', '.', $item->user->profile_image_url),
                ),
                'text' => $item->text,
                'midia' => $item->entities->media[0]->type == 'video' ? $item->entities->media[0]->video_info->variants[0]->url : $item->entities->media[0]->media_url,
            ));
        }
    }
}