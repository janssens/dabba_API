<?php

namespace App\Repository;

use App\Entity\Movement;
use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Movement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movement[]    findAll()
 * @method Movement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movement::class);
    }

    public function countAvoidedWaste(): ?int
    {
        return $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // /**
    //  * @return Movement[] Returns an array of Movement objects
    //  */
    public function findLastForRestaurant(Restaurant $restaurant,$limit = 10)
    {
        $stock_id = $restaurant->getStock()->getId();
        return $this->createQueryBuilder('m')
            ->andWhere('m.stock_from = :from')
            ->orWhere('m.stock_to = :to')
            ->setParameter('from', $stock_id)
            ->setParameter('to', $stock_id)
            ->orderBy('m.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Movement
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
