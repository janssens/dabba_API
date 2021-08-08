<?php

namespace App\Repository;

use App\Entity\TradeItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TradeItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method TradeItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method TradeItem[]    findAll()
 * @method TradeItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TradeItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TradeItem::class);
    }

    // /**
    //  * @return TradeItem[] Returns an array of TradeItem objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TradeItem
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
