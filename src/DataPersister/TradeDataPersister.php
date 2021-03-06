<?php

// src/DataPersister

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Order;
use App\Entity\Restaurant;
use App\Entity\Trade;
use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class TradeDataPersister implements ContextAwareDataPersisterInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $_entityManager;

    /**
     * @param Security
     */
    private $_security;

    /**
     * UserDataPersister constructor.
     * @param EntityManagerInterface $entityManager
     * @param Security $security
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->_entityManager = $entityManager;
        $this->_security = $security;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Trade;
    }

    public function persist($data, array $context = [])
    {
        /** @var $data Trade */
        if (!$data->getId()){
            /** @var User $user */
            $user = $this->_security->getUser();
            $data->setUser($user);
        }
        $this->_entityManager->persist($data);
        $this->_entityManager->flush();
    }

    public function remove($data, array $context = [])
    {
        $this->_entityManager->remove($data);
        $this->_entityManager->flush();
    }
}