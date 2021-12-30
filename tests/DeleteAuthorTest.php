<?php

use Symfony\Component\HttpFoundation\Response;

class DeleteAuthorTest extends BaseTest
{
    public function testDestroy()
    {
        self::$client->request(
            "DELETE",
            "/api/authors/{$this->getLastAuthorId()}",
            [],
            [],
            self::$header
        );

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
    }

    protected function tearDown(): void
    {
        $this->em->createQuery(
            'DELETE FROM App\Entity\Book b
             WHERE b.id = :id'
        )
        ->setParameter('id', $this->getLastBookId())
        ->execute();

        $this->em->createQuery(
            'DELETE FROM App\Entity\Order o
             WHERE o.id = :id'
        )
        ->setParameter('id', $this->getLastOrderId())
        ->execute();
    }
}