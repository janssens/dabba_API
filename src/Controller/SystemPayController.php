<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\PaymentToken;
use App\Entity\Transaction;
use App\Entity\User;
use App\Exception\AlreadyPaid;
use App\Exception\BadRequest;
use App\Exception\NotFound;
use App\Service\SystemPay;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use FOS\RestBundle\Controller\Annotations as Rest;

class SystemPayController extends AbstractController
{
    private $_lastCalculatedHash;

    /**
     * @Route("/ipn",name="app_ipn",methods={"POST"})
     */
    public function ipn(Request $request,LoggerInterface $logger){

        if (!$this->checkHash($request)) {
            throw new \Exception('invalid signature');
        }
        $data = $request->request->all();

        if ($data['kr-answer-type']=='V4/Payment'){

            $em = $this->getDoctrine()->getManager();

            $answer = json_decode($data['kr-answer']);
            $order_details = $answer->orderDetails;

            $mode = $order_details->mode;
            $system_pay_order_id = $order_details->orderId;
            /** @var Order $order */
            $order = $em->getRepository(Order::class)->findOneBySystemPayId($system_pay_order_id);
            if (!$order){
                throw new \Exception('Order #'.$system_pay_order_id.' not found');
            }
            if ($order_details->orderTotalAmount != $order_details->orderEffectiveAmount){
                throw new \Exception('Order is not fully paid');
            }
            if ($order->getAmount()*100 != $order_details->orderEffectiveAmount){
                throw new \Exception('order amount miss match');
            }

            $email = $answer->customer->email;
            $request_user = $em->getRepository(User::class)->findOneBy(['email'=>$email]);

            if ($order->getUser() != $request_user){
                throw new \Exception('user miss match');
            }

            $order->setState(Order::stateFromString($answer->orderStatus));

            //$orderCycle = $answer->orderCycle; //"CLOSED"

            foreach ($answer->transactions as $transaction){
                $transaction_id = $transaction->uuid;
                $exist = $em->getRepository(Transaction::class)->find($transaction_id);
                if (!$exist){
                    $this->createAliasFromTransaction($transaction,$order->getUser());
                    $trans = new Transaction();
                    $trans->setModeFromString($mode);
                    $trans->setShopId($transaction->shopId);
                    $trans->setUuid($transaction_id);
                    $trans->setAmount($transaction->amount/100);
                    $trans->setCurrency($transaction->currency);
                    $trans->setPaymentMethodTypeFromString($transaction->paymentMethodType);
                    $trans->setPaymentMethodToken($transaction->paymentMethodToken);
                    $trans->setStatusFromString($transaction->status);
                    if ($transaction->errorCode){
                        $trans->setErrorCode(intval($transaction->errorCode));
                        $trans->setErrorMessage($transaction->errorMessage);
                        $trans->setDetailedErrorCode($transaction->detailedErrorCode);
                        $trans->setDetailedErrorMessage($transaction->detailedErrorMessage);
                    }
                    if ($transaction->effectiveStrongAuthentication == "ENABLED"){
                        $trans->setEffectiveStrongAuthentication(true);
                    }else{
                        $trans->setEffectiveStrongAuthentication(false);
                    }
                    $trans->setDetailedStatusFromString($transaction->detailedStatus);
                    $trans->setOperationTypeFromString($transaction->operationType);
                    $trans->setCreationDate(date_create_from_format( \DateTimeInterface::W3C,$transaction->creationDate));
                    $trans->setMetadata($transaction->metadata);
                    $trans->setParent($order);
                    $em->persist($trans);
                }
            }
            $em->flush();

            $logger->info('IPN call',$data);

        }

        return $this->render('ipn.html.twig');
    }

    private function createAliasFromTransaction($transaction,User $user,$flush = false){
        if ($transaction->paymentMethodType == Transaction::TYPE_CARD && $transaction->paymentMethodToken){
            $uuid = $transaction->paymentMethodToken; //"eb62b1c418cc4535b1a2d101ef8e3548"
            $em = $this->getDoctrine()->getManager();
            $exist = $em->getRepository(PaymentToken::class)->find($uuid);
            if (!$exist){

                $card_details = $transaction->transactionDetails->cardDetails;

                $payment_token = new PaymentToken($uuid,$user);
                $payment_token->setBrand($card_details->effectiveBrand);
                $payment_token->setCountry($card_details->country);
                $payment_token->setExpiryMonth($card_details->expiryMonth);
                $payment_token->setExpiryYear($card_details->expiryYear);
                $payment_token->setPan($card_details->pan);

                $em->persist($payment_token);

                if ($flush){
                    $em->flush();
                }
            }
        }
    }

    /**
     * @Route(
     *     "/api/orders/{id}/pay/",
     *     name="api_pay_order",
     *     methods={"POST"},
     *     defaults={
     *          "_api_resource_class" = Order::class,
     *          "_api_item_operation_name" = "pay",
     *     })
     */
    public function api_pay_order(Order $order,Request $request,SystemPay $systemPay): Order
    {
        if ($order->getState()!=Order::STATE_NEW){
            throw new AlreadyPaid('You cannot pay an order twice !');
        }
        $post = json_decode($request->getContent());
        if (!$post || !$post->token_id){
            throw new BadRequest('please provide a valid input');
        }
        /** @var PaymentToken $token */
        $token = $this->getDoctrine()->getManager()->getRepository(PaymentToken::class)->find($post->token_id);
        if ($token){
            $r = $systemPay->payWithToken($order,$token);
            if (isset($r['orderStatus'])&&$r['orderStatus']=='PAID'){
                $order->setState(Order::STATE_PAID);
                if (isset($r['transactions'])&&isset($r['transactions'][0])&&isset($r['transactions'][0]['detailedStatus'])) {
                    $order->setStatus(Order::statusFromString($r['transactions'][0]['detailedStatus']));
                }
            }
        }else{
            throw new NotFound('this token is not found');
        }
        return $order;
    }

    /**
     * check kr-answer object signature
     */
    private function checkHash(Request $request, string $key = NULL)
    {
        $supportedHashAlgorithm = array('sha256_hmac');

        $data = $request->request->all();

        if (!isset($data['kr-hash-algorithm'])){
            throw new \Exception("Is not a valid request");
        }

        if (!isset($data['kr-answer'])){
            throw new \Exception("Is not a valid request");
        }

        /* check if the hash algorithm is supported */
        if (!in_array($data['kr-hash-algorithm'],  $supportedHashAlgorithm)) {
            throw new \Exception("hash algorithm not supported:" . $_POST['kr-hash-algorithm'] .". Update your SDK");
        }

        /* on some servers, / can be escaped */
        $krAnswer = str_replace('\/', '/', $data['kr-answer']);

        /* if key is not defined, we use kr-hash-key POST parameter to choose it */
        if (is_null($key)) {
            if ($_POST['kr-hash-key'] == "sha256_hmac") {
                $key = $this->getParameter('app.system_pay.hmac');
            } elseif ($_POST['kr-hash-key'] == "password") {
                $key = $this->getParameter('app.system_pay.client_secret');
            } else {
                throw new \Exception("invalid kr-hash-key POST parameter");
            }
        }

        $calculatedHash = hash_hmac('sha256', $krAnswer, $key);
        $this->_lastCalculatedHash = $calculatedHash;

        /* return true if calculated hash and sent hash are the same */
        return ($calculatedHash == $data['kr-hash']);
    }
}
