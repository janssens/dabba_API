<?php

namespace App\Repository;

use App\Entity\Zone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Zone|null find($id, $lockMode = null, $lockVersion = null)
 * @method Zone|null findOneBy(array $criteria, array $orderBy = null)
 * @method Zone[]    findAll()
 * @method Zone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zone::class);
    }

    public function number(): ?int
    {
        return $this->createQueryBuilder('f')
            ->select('count(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findDefault(): ?Zone
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.is_default = :val')
            ->setParameter('val', true)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findDefaultButThisOne(Zone $zone): ?Zone
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.is_default = :val')
            ->andWhere('z.id != :id')
            ->setParameter('val', true)
            ->setParameter('id', $zone->getId())
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
