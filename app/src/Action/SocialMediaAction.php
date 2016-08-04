<?php

namespace App\Action;

use App\Service\FacebookService;
use App\Service\InstagramService;
use App\Service\TwitterService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use FileSystemCache;
use Thapp\XmlBuilder\XMLBuilder;
use Thapp\XmlBuilder\Normalizer;
use Carbon\Carbon;

final class SocialMediaAction
{
    public function __construct()
    {
        setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
        date_default_timezone_set('America/Sao_Paulo');
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        FileSystemCache::$cacheDir = __DIR__ . '/../../../cache/tmp';
        $key = FileSystemCache::generateCacheKey('feed', null);
        $data = FileSystemCache::retrieve($key);

        $data = false;

        if($data === false)
        {

/*            $instagramService = new InstagramService();
            $twitterService = new TwitterService();*/
            $facebookService = new FacebookService();


            $data = array(
                $facebookService->getFeeds()
/*                $twitterService->getFeeds(),
                $instagramService->getFeeds()*/
            );
        }

        $xmlBuilder = new XmlBuilder('root');
        $xmlBuilder->setSingularizer(function ($name) {
            if ('itens' === $name) {
                return 'item';
            }
            return $name;
        });
        $xmlBuilder->load($data);
        $xml_output = $xmlBuilder->createXML(true);
        $response->write($xml_output);
        $response = $response->withHeader('content-type', 'text/xml');
        return $response;
    }
}