<?php

namespace App\Service;

use App\Entity\Order;
use GuzzleHttp\Client;
use JMS\Serializer\Serializer;
use Symfony\Component\Routing\Router;

class SystemPay
{
    private $systemPayClient;
    private $serializer;
    private $apiId;
    private $apiSecret;
    private $router;

    public function __construct(Client $systemPayClient, Serializer $serializer, $apiId, $apiSecret,Router $router)
    {
        $this->systemPayClient = $systemPayClient;
        $this->serializer = $serializer;
        $this->apiId = $apiId;
        $this->apiSecret = $apiSecret;
        $this->router = $router;
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

    public function test(string $value)
    {
        $uri = '/api-payment/V4/Charge/SDKTest';
        $body = json_encode(["value" => $value ]);
        return $this->withErrorHandling($uri,$body);
    }

    public function getTokenForOrder(Order $order){
        $uri = 'api-payment/V4/Charge/CreatePayment';
        $body = json_encode([
            "amount" => $order->getAmount()*100,
            "currency" => $order->getCurrency(),
            "orderId" =>  $order->getId(),
            "ipnTargetUrl" => $this->router->generate('app_ipn',[],Router::ABSOLUTE_URL),
            "customer" => [
                "email" => $order->getUser()->getEmail()
            ]]);
        $response = $this->withErrorHandling($uri,$body);
        if (isset($response['success'])){
            return $response['success']['formToken'];
        }else{
            return null;
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
            $response = $this->systemPayClient->post($uri,[
                'body' => $body,
                'auth' => [
                    $this->apiId,
                    $this->apiSecret
                ]]);
            return $this->handleResponse($response);
        } catch (\Exception $e) {
            //todo : Penser à logger l'erreur.
            //$this->logger->error('The weather API returned an error: '.$e->getMessage());
            return ['error' => 'error using system pay api'];
        }
    }
}