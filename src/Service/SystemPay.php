<?php

namespace App\Service;

use App\Entity\Order;
use GuzzleHttp\Client;
use JMS\Serializer\Serializer;
use Monolog\Logger;
use Symfony\Component\Routing\Router;

class SystemPay
{
    private $systemPayClient;
    private $serializer;
    private $apiId;
    private $apiSecret;
    private $apiPublic;
    private $apiHmac;
    private $router;
    private $logger;

    public function __construct(Client $systemPayClient, Serializer $serializer, $apiId, $apiSecret, $apiPublic,$apiHmac,Router $router,Logger $logger)
    {
        $this->systemPayClient = $systemPayClient;
        $this->serializer = $serializer;
        $this->apiId = $apiId;
        $this->apiSecret = $apiSecret;
        $this->apiPublic = $apiPublic;
        $this->apiHmac = $apiHmac;
        $this->router = $router;
        $this->logger = $logger;
    }

    private function handleResponse($response){
        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        if ($data["status"]!="SUCCESS"){ //or ERROR
            return [
                'error' => $data["answer"]
            ];
        }
        return [
            'success' => $data['answer'],
        ];
    }

    public function getPublicKey(){
        return $this->apiPublic;
    }

    public function test(string $value)
    {
        $uri = '/api-payment/V4/Charge/SDKTest';
        $body = json_encode(["value" => $value ]);
        return $this->withErrorHandling($uri,$body);
    }

    public function getOrderHash(Order $order){
        return hash_hmac('sha256', $order->getSystemPayId(), $this->apiHmac);
    }

    public function getTokenForOrder(Order $order){
        $uri = '/api-payment/V4/Charge/CreatePayment';
        $body = json_encode([
            "amount" => $order->getAmount()*100,
            "currency" => $order->getCurrency(),
            "orderId" =>  $order->getSystemPayId(),
            "ipnTargetUrl" => $this->router->generate('app_ipn',[],0),
            "customer" => [
                "email" => $order->getUser()->getEmail()
            ]]);
        $this->logger->info('System Pay '.$uri.' '.$body);
        $response = $this->withErrorHandling($uri,$body);
        if (isset($response['success'])){
            return $response['success']['formToken'];
        }else{
            throw new \Exception('System Pay form token cannot be generated. '.$response['error']);
            //return null;
        }
    }

    public function createTokenFromTransaction(string $uuid){
        $uri = '/api-payment/V4/Charge/CreateTokenFromTransaction';
        $body = json_encode(["uuid" => $uuid ]);
        return $this->withErrorHandling($uri,$body);
    }

    private function withErrorHandling($uri,$body) :array
    {
        try {
            $params = [
                'body' => $body,
                'auth' => [
                    $this->apiId,
                    $this->apiSecret
                ]];
            $response = $this->systemPayClient->post($uri,$params);
            return $this->handleResponse($response);
        } catch (\Exception $e) {
            $this->logger->error('The System Pay API returned an error: '.$e->getMessage(),$params);
            return ['error' => 'Error using System Pay API. See logs for details.'];
        }
    }
}