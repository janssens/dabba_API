<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\PaymentToken;
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
        return $this->apiId.':'.$this->apiPublic;
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
            "formAction" => 'ASK_REGISTER_PAY',
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
            throw new \Exception('System Pay form token cannot be generated. '.print_r($response['error'],true));
            //return null;
        }
    }

    public function payWithToken(Order $order,PaymentToken $paymentToken){
        if ($order->getUser()!=$paymentToken->getUser()){
            throw new \Exception('Users can only pay for their own orders.');
        }
        $uri = '/api-payment/V4/Charge/CreatePayment';
        $body = json_encode([
            "amount" => $order->getAmount()*100,
            "currency" => $order->getCurrency(),
            "formAction" => 'SILENT',
            "paymentMethodToken" => $paymentToken->getUuid(),
            "orderId" =>  $order->getSystemPayId(),
            "ipnTargetUrl" => $this->router->generate('app_ipn',[],0),
            "customer" => [
                "email" => $order->getUser()->getEmail()
            ]]);
        $this->logger->info('System Pay '.$uri.' '.$body);
        $response = $this->withErrorHandling($uri,$body);
        if (isset($response['success'])){
            return $response['success'];
        }else{
            $error = (isset($response['error']['errorCode'])) ? '['.$response['error']['errorCode'].']' : '';
            $error .= (isset($response['error']['errorMessage'])) ? ' : '.$response['error']['errorMessage'] : '';
            $error .= (isset($response['error']['detailedErrorMessage'])) ? ' ('.$response['error']['detailedErrorMessage'].')' : '';
            throw new \Exception($error);
            //return null;
        }
    }

    public function getTokenInfo(string $tokenId ){
        $uri = '/api-payment/V4/Token/Get';
        $body = json_encode(["paymentMethodToken" => $tokenId]);
        $this->logger->info('System Pay '.$uri.' '.$body);
        $response = $this->withErrorHandling($uri,$body);
        if (isset($response['success'])){
            return $response['success'];
        }else{
            if (isset($response['error']['errorCode'])&&$response['error']['errorCode']=='PSP_030'){ //payment token not found
                return [];
            }
            $error = (isset($response['error']['errorCode'])) ? '['.$response['error']['errorCode'].']' : '';
            $error .= (isset($response['error']['errorMessage'])) ? ' : '.$response['error']['errorMessage'] : '';
            $error .= (isset($response['error']['detailedErrorMessage'])) ? ' ('.$response['error']['detailedErrorMessage'].')' : '';
//            throw new \Exception('SystemPay Api call failed. '.$error);
            $this->logger->error('SystemPay Api call failed. '.$error);
            return null;
        }
    }

    public function removeToken(PaymentToken $paymentToken){
        $uri = '/api-payment/V4/Token/Cancel';
        $body = json_encode(["paymentMethodToken" => $paymentToken->getUuid()]);
        $this->logger->info('System Pay '.$uri.' '.$body);
        $response = $this->withErrorHandling($uri,$body);
        if (isset($response['success'])){
            return $response['success'];
        }else{
            $error = (isset($response['error']['errorCode'])) ? '['.$response['error']['errorCode'].']' : '';
            $error .= (isset($response['error']['errorMessage'])) ? ' : '.$response['error']['errorMessage'] : '';
            $error .= (isset($response['error']['detailedErrorMessage'])) ? ' ('.$response['error']['detailedErrorMessage'].')' : '';
            throw new \Exception($error);
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