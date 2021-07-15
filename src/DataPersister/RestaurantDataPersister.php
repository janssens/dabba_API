<?php

// src/DataPersister

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Restaurant;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class RestaurantDataPersister implements ContextAwareDataPersisterInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $_entityManager;

    /**
     * @param Request
     */
    private $_request;

    /**
     * @param Security
     */
    private $_security;

    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        Security $security
    ) {
        $this->_entityManager = $entityManager;
        $this->_request = $requestStack->getCurrentRequest();
        $this->_security = $security;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Restaurant;
    }


    public function persist($data, array $context = [])
    {
        if ($this->_request->getMethod() !== 'POST') {
            //$data->setUpdatedAt(new \DateTime());
        }

        if ($data->getZone() === null){
            /** @var User $user */
            $user = $this->_security->getUser();
            $data->setZone($user->getZone());
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