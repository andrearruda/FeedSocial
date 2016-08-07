<?php

namespace App\Service;

use Facebook;
use Stringy\Stringy as S;

class FacebookService extends FeedsServiceAbstract
{
    private $username = 'rio2016pt';
    private $length = 5;
    private $acessToken = 'EAAPfK6SRjpgBAEvFvUr4y9GjQZBJLVCvXO0h7hBtieZBmU383zKyB9qqNaf4svJA7lc2OKQwnZBT3FfTgTiPBZA8JV2TePFANKirfXJSnwVhBI4ZCvyHJtXwR9CTZA1ZBxwkPx8IR4uuMZAXKa4ufLzLhDrbzUoZCgZAMZD';

    public function __construct()
    {
        $fb = new Facebook\Facebook([
            'app_id' => '1089803467722392',
            'app_secret' => 'f40c536eca0b3db7a22c3fa8f373d873',
            'default_graph_version' => 'v2.7',
            'default_access_token' => $this->acessToken
        ]);

        $request = $fb->get('/' . $this->username . '?fields=feed.limit(' . $this->length . '){id, created_time, from{name, username, picture.width(500)}, type, message, description, attachments{media{image{src}}}, source}');
        $response = $request->getGraphPage()->getField('feed');

        foreach($response as $item)
        {
            if(($item->getField('type') == 'photo' || $item->getField('type') == 'video') && $item->getField('message') != '')
            {
                /**
                 * @var $date \DateTime
                 */
                $date = $item->getField('created_time')->setTimezone(new \DateTimeZone('America/Sao_paulo'));

                $picture_data = array(
                    'name' => 'facebook_' . date('Ymd') . '.jpg',
                    'source' => $item->getField('from')->getField('picture')->getField('url'),
                    'path' => __DIR__ . '/../../../data/pictures/'
                );

                if (!file_exists($picture_data['path'] . $picture_data['name']))
                {
                    file_put_contents($picture_data['path'] . $picture_data['name'], file_get_contents($picture_data['source']));
                }

                if (!is_null($item->getField('attachments')))
                {
                    $image_data = array(
                        'name' => $item->getField('id') . '.jpg',
                        'source' => $item->getField('attachments')->getField('0')->getField('media')->getField('image')->getField('src'),
                        'path' => __DIR__ . '/../../../data/images/'
                    );

                    if (!file_exists($image_data['path'] . $image_data['name']))
                    {
                        file_put_contents($image_data['path'] . $image_data['name'], file_get_contents($image_data['source']));
                    }

                    $image_url = 'http://' . $_SERVER['HTTP_HOST'] . '/olimpiadas/social_media/data/images/' . $image_data['name'];
                }
                else
                {
                    $image_url = '';
                }

                if($item->getField('type') == 'video')
                {
                    $video_data = array(
                        'name' => $item->getField('id') . '.' . pathinfo(preg_replace('/\?.*/', '', $item->getField('source')), PATHINFO_EXTENSION),
                        'source' => $item->getField('source'),
                        'path' => __DIR__ . '/../../../data/videos/'
                    );

                    if (!file_exists($video_data['path'] . $video_data['name']))
                    {
                        file_put_contents($video_data['path'] . $video_data['name'], file_get_contents($video_data['source']));
                    }

                    $video_url = 'http://' . $_SERVER['HTTP_HOST'] . '/olimpiadas/social_media/data/videos/' . $video_data['name'];
                }
                else
                {
                    $video_url = '';
                }

                $text = array_shift(preg_split("/\\r\\n|\\r|\\n/", $item->getField('message')));

                $this->addFeed(array(
                    'created' => $date->format('Y-m-d H:i:s'),
                    'typefeed' => 'facebook',
                    'user' => array(
                        'name' => $item->getField('from')->getField('name'),
                        'username' => $item->getField('from')->getField('username'),
                        'picture' => 'http://' . $_SERVER['HTTP_HOST'] . '/olimpiadas/social_media/data/pictures/' . $picture_data['name'],
                    ),
                    'text' => (string) S::create($text)->safeTruncate(180, '...'),
                    'midia' => array(
                        'type' => $item->getField('type') == 'photo' ? 'image' : $item->getField('type'),
                        'image' => $image_url,
                        'video' => $video_url
                    ),
                ));
            }
        }
    }
}