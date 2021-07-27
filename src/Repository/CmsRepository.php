<?php

namespace App\Repository;

use App\Entity\Cms;
use App\Entity\Zone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cms|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cms|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cms[]    findAll()
 * @method Cms[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CmsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cms::class);
    }

     /**
      * @return Cms[] Returns an array of Cms objects
      */
    public function findByZoneCategory(Zone $zone,string $category,bool $only_public = false)
    {
        $q = $this->createQueryBuilder('c')
            ->leftJoin('c.zone', 'z')
            ->andWhere('z.id = :zone')
            ->andWhere('c.category = :category')
            ->setParameter('zone', $zone->getId())
            ->setParameter('category', $category);

        if ($only_public){
            $q = $q->andWhere('c.is_public = :public')
                ->setParameter('public', 1);
        }

        return $q->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

}
