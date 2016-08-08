<?php

namespace App\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use FileSystemCache;
use Thapp\XmlBuilder\XMLBuilder;
use Thapp\XmlBuilder\Normalizer;
use Carbon\Carbon;

final class OlympicScheduleHourAllAction
{
    /*
     * URL
     * http://olimpiadas.uol.com.br/resultados-e-agenda/?debug=true
     * http://jsuol.com.br/c/olimpiadas/api/?modulo=gestor-olimpico&v=2
     *
     */
    private $jsonUrl = 'http://jsuol.com.br/c/olimpiadas/api/?modulo=gestor-olimpico&v=2';
    private $dateTimeCurrent;

    public function __construct()
    {
        setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
        date_default_timezone_set('America/Sao_Paulo');

        $this->dateTimeCurrent = Carbon::createFromFormat('d/m/Y H:i', Carbon::now()->format('d/m/Y H:i'));
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        FileSystemCache::$cacheDir = __DIR__ . '/../../../cache/tmp';
        $key = FileSystemCache::generateCacheKey('schedule.hour.all', null);
        $data = FileSystemCache::retrieve($key);

        if($data === false)
        {
            $data = array(
                'info' => array(
                    'date' => $this->dateTimeCurrent->format('Y-m-d'),
                    'datestr' => $this->dateTimeCurrent->formatLocalized('%d %B'),
                    'createdat' => date('Y-m-d H:i:s'),
                ),
                'itens' => array()
            );
            $json = json_decode(file_get_contents($this->getJsonUrl()), true);

            $events = $json[$this->dateTimeCurrent->format('d/m/Y')];

            foreach($events as $i => $event)
            {
                foreach($event['itens'] as $j => $category)
                {
                    foreach($category['itens'] as $k => $item)
                    {
                        $evento = explode(' - ', $item['evento']);
                        if(count($evento) > 1)
                        {
                            $step = array_shift($evento);
                            $category = implode(' - ', $evento);
                        }
                        else
                        {
                            $category = $item['evento'];
                            $step = $item['fase'];
                        }

                        $data['itens'][] = array(
                            'modality' => $item['modalidade'],
                            'category' => $category,
                            'icon' => 'http://' .$_SERVER['HTTP_HOST'] . '/rio2016/data/pictograma/' . $event['icon'] . '.png',
                            'step' => $step,
                            'info' => array(
                                'title' => $item['titulo'],
                                'date' => $item['data'],
                                'hour' => $item['hora'],
                                'brasil' => $item['brasil_na_disputa'],
                                'local' => trim($item['local'])
                            )
                        );
                    }
                }
            }

            ksort($data['itens']);

            FileSystemCache::store($key, $data, 1800);
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

    /**
     * @return string
     */
    public function getJsonUrl()
    {
        return $this->jsonUrl;
    }

}