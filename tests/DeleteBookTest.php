<?php

use Symfony\Component\HttpFoundation\Response;

class DeleteBookTest extends BooksTest
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