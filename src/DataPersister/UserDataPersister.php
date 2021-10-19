<?php

// src/DataPersister

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Entity\Zone;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserDataPersister implements ContextAwareDataPersisterInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $_entityManager;

    /**
     * @var Request
     */
    private $_request;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $_passwordEncoder;

    /**
     * @var EmailVerifier
     */
    private $_emailVerifier;


    /**
     * @var ParameterBagInterface
     */
    private $_params;

    /**
     * UserDataPersister constructor.
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EmailVerifier $emailVerifier
     * @param ParameterBagInterface $params
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        UserPasswordEncoderInterface $passwordEncoder,
        EmailVerifier $emailVerifier,
        ParameterBagInterface $params
    ) {
        $this->_entityManager = $entityManager;
        $this->_request = $requestStack->getCurrentRequest();
        $this->_passwordEncoder = $passwordEncoder;
        $this->_emailVerifier = $emailVerifier;
        $this->_params = $params;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof User;
    }


    /**
     * @param $data
     * @param array $context
     * @return object|void
     */
    public function persist($data, array $context = [])
    {
        /** @var $data User */
        $is_new = !$data->getId();

        if ($data->getPlainPassword()) {
            $data->setPassword(
                $this->_passwordEncoder->encodePassword(
                    $data,
                    $data->getPlainPassword()
                )
            );

            $data->eraseCredentials();
        }

        if ($is_new){
            if (!$data->getZone())
                $data->setZone($this->_entityManager->getRepository(Zone::class)->findDefault());
        }

        $this->_entityManager->persist($data);
        $this->_entityManager->flush();

        if ($is_new && !$data->isVerified()){
            // generate a signed url and email it to the user
            $this->_emailVerifier->sendEmailConfirmation('app_verify_email', $data,
                (new TemplatedEmail())
                    ->from(new Address($this->_params->get('app.transactional_mail_sender'), 'Dabba consigne'))
                    ->to($data->getEmail())
                    ->subject('Confirme ton adresse e-mail pour créer ton compte dabba')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email
        }
    }

    public function remove($data, array $context = [])
    {
        $this->_entityManager->remove($data);
        $this->_entityManager->flush();
    }
}