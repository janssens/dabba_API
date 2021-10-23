<?php

namespace App\Repository;

use App\Entity\ExternalWasteSave;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExternalWasteSave|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExternalWasteSave|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExternalWasteSave[]    findAll()
 * @method ExternalWasteSave[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExternalWasteSaveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExternalWasteSave::class);
    }

    // /**
    //  * @return ExternalWasteSave[] Returns an array of ExternalWasteSave objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ExternalWasteSave
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
