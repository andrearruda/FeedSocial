<?php

namespace App\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use FileSystemCache;
use Thapp\XmlBuilder\XMLBuilder;
use Thapp\XmlBuilder\Normalizer;
use Carbon\Carbon;

final class OlympicScheduleDayAction
{
    /*
     * URL
     * http://olimpiadas.uol.com.br/resultados-e-agenda/?debug=true
     * http://jsuol.com.br/c/olimpiadas/api/?modulo=gestor-olimpico&v=2
     *
     */
    private $jsonUrl = 'http://jsuol.com.br/c/olimpiadas/api/?modulo=gestor-olimpico&v=2';
    private $dateCurrent;

    public function __construct()
    {
        setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
        date_default_timezone_set('America/Sao_Paulo');

        $this->dateCurrent = Carbon::createFromFormat('d/m/Y H:i', Carbon::now()->format('d/m/Y 00:00'));
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        FileSystemCache::$cacheDir = __DIR__ . '/../../../cache/tmp';
        $key = FileSystemCache::generateCacheKey('schedule.day', null);
        $data = FileSystemCache::retrieve($key);

        if($data === false)
        {
            $data = array(
                'info' => array(
                    'date' => $this->dateCurrent->format('Y-m-d'),
                    'datestr' => $this->dateCurrent->formatLocalized('%d %B')
                ),
                'itens' => array()
            );
            $json = json_decode(file_get_contents($this->getJsonUrl()), true);

            $events = $json[$this->dateCurrent->format('d/m/Y')];

            $ac_i = 0;
            foreach($events as $i => $event)
            {
                $data['itens'][$ac_i] = array(
                    'label' => $event['label'],
                    'icons' => 'http://' .$_SERVER['HTTP_HOST'] . '/rio2016/data/pictograma/' . $event['icon'] . '.png',
                    'itens' => array()
                );

                $ac_j = 0;
                foreach($event['itens'] as $j => $category)
                {
                    $data['itens'][$ac_i]['itens'][$ac_j] = array(
                        'label' => $category['label'],
                        'itens' => array()
                    );

                    $ac_k = 0;
                    foreach($category['itens'] as $k => $item)
                    {
                        $data['itens'][$ac_i]['itens'][$ac_j]['itens'][] = array(
                            'hour' => $item['hora'],
                            'step' => $item['fase'],
                            'description' => $item['descricao_prova'],
                            'info' => array(
                                'brasil' => $item['brasil_na_disputa'],
                                'local' => $item['local']
                            )
                        );
                    }

                    $ac_j++;
                }

                $ac_i++;
            }

            FileSystemCache::store($key, $data, 1800);
        }

        $xmlBuilder = new XmlBuilder('root');
        $xmlBuilder->setSingularizer(function ($name) {
            if ('itens' === $name) {
                return 'item';
            }
            if ('icons' === $name) {
                return 'icon';
            }
            return $name;
        });
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