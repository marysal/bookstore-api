<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }


    /**
     * @param array $params
     * @return Book Returns an array of Book objects
     */
    public function findByFields(array $params)
    {
        $query = $this->createQueryBuilder('b');

        if(isset($params['title'])) {
            $query->andWhere('b.title LIKE :title')->setParameter('title', $params['title']);
        }

        if(isset($params['description'])) {
            $query->andWhere('b.description LIKE :description')->setParameter('description', $params['description']);
        }

        if(isset($params['type'])) {
            $query->andWhere('b.type LIKE :type')->setParameter('type', $params['type']);
        }

        return $query->getQuery()->execute();
    }

}
