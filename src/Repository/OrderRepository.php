<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @param array $params
     * @return Order Returns an array of Order objects
     */
    public function findByFields(array $params)
    {
        $query = $this->createQueryBuilder('b');

        if(isset($params['phone'])) {
            $query->andWhere('b.phone LIKE :phone')->setParameter('phone', $params['phone']);
        }

        if(isset($params['address'])) {
            $query->andWhere('b.address LIKE :address')->setParameter('address', $params['address']);
        }

        if(isset($params['status'])) {
            $query->andWhere('b.status LIKE :status')->setParameter('status', $params['status']);
        }

        return $query->getQuery()->execute();
    }
}
