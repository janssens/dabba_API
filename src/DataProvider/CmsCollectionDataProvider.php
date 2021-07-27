<?php
// src/DataProvider/CmsCollectionDataProvider.php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Cms;
use App\Entity\Zone;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

final class CmsCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{

    private $managerRegistry;
    /**
     * @param Security
     */
    private $_security;

    public function __construct(ManagerRegistry $managerRegistry,Security $security)
    {
        $this->managerRegistry = $managerRegistry;
        $this->_security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Cms::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $category = null;
        if (isset($context['filters'])&&isset($context['filters']['category'])){
            $category = $context['filters']['category'];
        }
        if (!in_array($category,array(Cms::CATEGORY_HOME,Cms::CATEGORY_MY_DABBA))){
            $category = Cms::CATEGORY_HOME;
        }
        //zone
        $mz = $this->managerRegistry->getManagerForClass(Zone::class);
        if ($user = $this->_security->getUser()){
            $zone = $user->getZone();
        }else{
            $zone = null;
        }
        if (isset($context['filters'])&&isset($context['filters']['zone.id'])){
            $zone_id = $context['filters']['zone.id'];
            $zone = $mz->getRepository(Zone::class)->find($zone_id);
        }
        if (!$zone){
            $zone = $mz->getRepository(Zone::class)->findDefault();
        }
        $manager = $this->managerRegistry->getManagerForClass($resourceClass);
        $repository = $manager->getRepository($resourceClass);
        if ($user){
            return $repository->findByZoneCategory($zone,$category);
        }
        return $repository->findByZoneCategory($zone,$category,true);
    }

}