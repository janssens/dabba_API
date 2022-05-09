<?php

namespace App\Repository;

use App\Entity\AccessToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccessToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccessToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccessToken[]    findAll()
 * @method AccessToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccessTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessToken::class);
    }

    public function save(AccessToken $accessToken): void
    {
        $this->_em->persist($accessToken);
        $this->_em->flush();
    }

    /**
     * @param User $user
     * @return AccessToken[]
     */
    public function findAllActiveForUser(User $user)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.userId = :userId')
            ->andWhere('a.revoked = 0')
            ->setParameter('userId', $user->getId())
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }
}