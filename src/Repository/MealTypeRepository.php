<?php

namespace App\Repository;

use App\Entity\MealType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MealType|null find($id, $lockMode = null, $lockVersion = null)
 * @method MealType|null findOneBy(array $criteria, array $orderBy = null)
 * @method MealType[]    findAll()
 * @method MealType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MealTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MealType::class);
    }

    // /**
    //  * @return MealType[] Returns an array of MealType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MealType
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
