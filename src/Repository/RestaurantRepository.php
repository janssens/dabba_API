<?php

namespace App\Repository;

use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Restaurant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Restaurant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Restaurant[]    findAll()
 * @method Restaurant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RestaurantRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Restaurant::class);
    }

    public function search($term, $order = 'asc', $limit = 20, $offset = 0)
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->select('r')
            ->orderBy('r.name', $order)
        ;

        if ($term) {
            $qb
                ->where('r.name LIKE ?1')
                ->setParameter(1, '%'.$term.'%')
            ;
        }

        return $this->paginate($qb, $limit, $offset);
    }

}