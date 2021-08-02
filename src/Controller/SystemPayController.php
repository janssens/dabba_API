<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Transaction;
use App\Entity\User;
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
                        $trans->setErrorCode($transaction->errorCode);
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
