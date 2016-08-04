<?php

namespace App\Service;

use Facebook;

class FacebookService extends FeedsServiceAbstract
{
    private $username = 'rio2016';
    private $length = 10;

    public function __construct()
    {
        $fb = new Facebook\Facebook([
            'app_id' => '1089803467722392',
            'app_secret' => 'f40c536eca0b3db7a22c3fa8f373d873',
            'default_graph_version' => 'v2.7',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        $permissions = [];

        try {
            if (isset($_SESSION['facebook_access_token']))
            {
                $accessToken = $_SESSION['facebook_access_token'];
            }
            else
            {
                $accessToken = $helper->getAccessToken();
            }
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $accessToken = 'EAAPfK6SRjpgBAEvFvUr4y9GjQZBJLVCvXO0h7hBtieZBmU383zKyB9qqNaf4svJA7lc2OKQwnZBT3FfTgTiPBZA8JV2TePFANKirfXJSnwVhBI4ZCvyHJtXwR9CTZA1ZBxwkPx8IR4uuMZAXKa4ufLzLhDrbzUoZCgZAMZD';

        if (isset($accessToken))
        {
            header('content-type: text/plain;');


            if (isset($_SESSION['facebook_access_token']))
            {
                $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
            }
            else
            {
                $_SESSION['facebook_access_token'] = (string) $accessToken;
                $oAuth2Client = $fb->getOAuth2Client();

                $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);

                $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
                $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
            }

            $page = $fb->get('/rio2016pt?fields=username,picture.width(500),cover');

            print_r($page->getGraphNode()->asArray());
            var_dump($_SESSION['facebook_access_token']);
        }
        else
        {
            $loginUrl = $helper->getLoginUrl('http://conteudo.farolsign.com.br/olimpiadas/social_media/socialmedia', $permissions);
            echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
        }

/*        var_dump($helper);
        var_dump($fb->getOAuth2Client()->getAccessTokenFromCode());*/

        /*
         * AQDGWPxeETtyPWpNWUPIL95gvFcAhvWmrW1xbpezWv9tk3xodR-UtyR_oAjcAE3Z-5wwGyqahH3O7NPHxqWktdWlnR8ImR-0LSMlT82RtoqF2dySRvDiLMsorY6LHCo25hzHHvoNFnKPtsS6Lcqls9ypU_YuZfkneTtw3QQcxHmmmfMi5vo5pBzM4DaitsQQMqBNyBBHOl0HVG763_kDMOnne12wEiePknA-rYXW8uAaTnpldMSMfbzH7hfInPrPgCDPVptmPUWHrrizROnyDr3DwIRXsen6z5Nw3Z4-asSFNEiskXQ6ax5DNif90jzRhWZKF56EkBDUe1PfK7HO61B5
         */

        die;
    }
}