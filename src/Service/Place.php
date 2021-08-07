<?php

namespace App\Service;

use GuzzleHttp\Client;
use JMS\Serializer\Serializer;
use Monolog\Logger;

class Place
{
    private $placeClient;
    private $serializer;
    private $apiKey;
    private $logger;

    public function __construct(Client $placeClient, Serializer $serializer, $apiKey, Logger $logger)
    {
        $this->placeClient = $placeClient;
        $this->serializer = $serializer;
        $this->apiKey = $apiKey;
        $this->logger = $logger;
    }

    public function search($name,$full_address){
        $uri = '/maps/api/place/findplacefromtext/json?inputtype=textquery&input='.urlencode($name.' '.$full_address).'&key='.$this->apiKey
            .'&fields=formatted_address,geometry,name,place_id';
        try {
            $response = $this->placeClient->get($uri);
        } catch (\Exception $e) {
            $this->logger->error('The google place API returned an error: '.$e->getMessage());
            return ['error' => 'error using google maps api'];
        }

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        if ($data["status"]=="REQUEST_DENIED"){
            return [
                'error' => $data["error_message"]
            ];
        }
        if ($data['status']=="ZERO_RESULTS"||!isset($data["candidates"])||count($data['candidates'])==0){
            return [
                'error' => 'no result'
            ];
        }
        if ($data["status"]!="OK"){
            return [
                'error' => $data["error_message"]
            ];
        }
        return [
            'success' => $data["candidates"],
        ];
    }

    public function getDetails($place_id){
        $uri = '/maps/api/place/details/json?place_id='.$place_id.'&key='.$this->apiKey.'&language=fr'
            .'&fields=formatted_phone_number,opening_hours,website';
        try {
            $response = $this->placeClient->get($uri);
        } catch (\Exception $e) {
            $this->logger->error('The google place API returned an error: '.$e->getMessage());
            return ['error' => 'error using google maps api'];
        }

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        if ($data["status"]=="REQUEST_DENIED"){
            return [
                'error' => $data["error_message"]
            ];
        }
        if ($data["status"]!="OK"){
            return [
                'error' => $data["error_message"]
            ];
        }
        return [
            'success' => $data["result"],
        ];
    }

    public function getOpenningHours($place_id)
    {
        $uri = '/maps/api/place/details/json?place_id='.$place_id.'&hl=fr&gl=FR&fields=opening_hours&key='.$this->apiKey;

        try {
            $response = $this->placeClient->get($uri);
        } catch (\Exception $e) {
            $this->logger->error('The google place API returned an error: '.$e->getMessage());
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