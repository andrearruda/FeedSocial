<?php

namespace App\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use FileSystemCache;
use Thapp\XmlBuilder\XMLBuilder;
use Thapp\XmlBuilder\Normalizer;

final class MedalBoardAction
{
	/*
	 * URL
	 * http://olimpiadas.uol.com.br/quadro-de-medalhas/?debug=true
	 * http://jsuol.com.br/c/esporte/olimpiadas/medals-table-embed/data.json
	 *
	 */
    private $jsonUrl = 'http://jsuol.com.br/c/esporte/olimpiadas/medals-table-embed/data.json';

    public function __invoke(Request $request, Response $response, $args)
    {
        FileSystemCache::$cacheDir = __DIR__ . '/../../../cache/tmp';
        $key = FileSystemCache::generateCacheKey('cache', null);
        $data = FileSystemCache::retrieve($key);

        $data = false;

        if($data === false)
        {
            $json = json_decode(file_get_contents($this->getJsonUrl()));

            $data = array();
            foreach($json->order as $i => $item)
            {
                if($item == 'BRA')
                {
                    $data[] = array(
                        'position' => ($i+1),
                        'country' => $json->countries->$item->pais,
                        'medals' => array(
                            'gold' => $json->countries->$item->score->ouro,
                            'silver' => $json->countries->$item->score->prata,
                            'bronze' => $json->countries->$item->score->bronze,
                            'total' => $json->countries->$item->score->total
                        )
                    );
                }

                if($i < 3)
                {
                    $data[] = array(
                        'position' => ($i+1),
                        'country' => $json->countries->$item->pais,
                        'medals' => array(
                            'gold' => $json->countries->$item->score->ouro,
                            'silver' => $json->countries->$item->score->prata,
                            'bronze' => $json->countries->$item->score->bronze,
                            'total' => $json->countries->$item->score->total
                        )
                    );
                }
            }

            FileSystemCache::store($key, $data, 1800);
        }

        $xmlBuilder = new XmlBuilder('root');
        $xmlBuilder->load($data);
        $xml_output = $xmlBuilder->createXML(true);
        $response->write($xml_output);
        $response = $response->withHeader('content-type', 'text/xml');
        return $response;
    }

    /**
     * @return string
     */
    public function getJsonUrl()
    {
        return $this->jsonUrl;
    }
}