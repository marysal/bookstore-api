<?php

use App\Tests\Books;
use Symfony\Component\HttpFoundation\Response;

class DeleteBookTest extends Books
{
    public function testDestroy()
    {
        self::$client->request(
            "DELETE",
            "/api/books/{$this->getLastBookId()}",
            [],
            [],
            self::$header
        );

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());

        $book = $this->em->createQuery(
            'SELECT b FROM App\Entity\Book b
             WHERE b.id = :id'
        )
        ->setParameter('id', $this->getLastBookId())
        ->execute();

        $this->assertEmpty($book);
    }

    protected function tearDown(): void
    {
        $this->em->createQuery(
            'DELETE FROM App\Entity\Author a
             WHERE a.id = :id'
        )
        ->setParameter('id', $this->getLastAuthorId())
        ->execute();

        $this->em->createQuery(
            'DELETE FROM App\Entity\Order o
             WHERE o.id = :id'
        )
        ->setParameter('id', $this->getLastOrderId())
        ->execute();
    }
}