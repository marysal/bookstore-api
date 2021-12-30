<?php

use Symfony\Component\HttpFoundation\Response;

class DeleteOrderTest extends BaseTest
{
    public function testDestroy()
    {
        self::$client->request(
            "DELETE",
            "/api/orders/{$this->getLastOrderId()}",
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
            'DELETE FROM App\Entity\Author a
             WHERE a.id = :id'
        )
        ->setParameter('id', $this->getLastAuthorId())
        ->execute();
    }
}