<?php

namespace App\Repository;

use App\Entity\WalletAdjustment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WalletAdjustment|null find($id, $lockMode = null, $lockVersion = null)
 * @method WalletAdjustment|null findOneBy(array $criteria, array $orderBy = null)
 * @method WalletAdjustment[]    findAll()
 * @method WalletAdjustment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WalletAdjustmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WalletAdjustment::class);
    }

    // /**
    //  * @return WalletAdjustment[] Returns an array of WalletAdjustment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WalletAdjustment
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
