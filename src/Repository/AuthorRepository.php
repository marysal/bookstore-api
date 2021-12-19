<?php

namespace App\Repository;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Author|null find($id, $lockMode = null, $lockVersion = null)
 * @method Author|null findOneBy(array $criteria, array $orderBy = null)
 * @method Author[]    findAll()
 * @method Author[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    /**
     * @return Author Returns author
     */
    public function findOne()
    {
        $query = $this->createQueryBuilder('b');

        $query->setMaxResults(1);

        return $query->getQuery()->execute();
    }

    /**
     * @param array $params
     * @return Author Returns an array of Author objects
     */
    public function findByFields(array $params)
    {
        $query = $this->createQueryBuilder('b');

        if(isset($params['name'])) {
            $query->andWhere('b.name LIKE :name')->setParameter('name', $params['name']);
        }

        return $query->getQuery()->execute();
    }
}
