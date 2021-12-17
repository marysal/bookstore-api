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
     * @param array $params
     * @return Book Returns an array of Book objects
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
