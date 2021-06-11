<?php

namespace App\Service;

use GuzzleHttp\Client;
use JMS\Serializer\Serializer;

class Place
{
    private $placeClient;
    private $serializer;
    private $apiKey;

    public function __construct(Client $placeClient, Serializer $serializer, $apiKey)
    {
        $this->placeClient = $placeClient;
        $this->serializer = $serializer;
        $this->apiKey = $apiKey;
    }

    public function getOpenningHours($place_id)
    {
        $uri = '/maps/api/place/details/json?place_id='.$place_id.'&hl=fr&gl=FR&fields=opening_hours&key='.$this->apiKey;

        try {
            $response = $this->placeClient->get($uri);
        } catch (\Exception $e) {
            //todo : Penser à logger l'erreur.
            //$this->logger->error('The weather API returned an error: '.$e->getMessage());
            return ['error' => 'error using google maps api'];
        }

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        if ($data["status"]=="REQUEST_DENIED"){
            return [
                'error' => $data["error_message"]
            ];
        }
        return [
            'weekday_text' => $data['weekday_text'],
        ];
    }
}