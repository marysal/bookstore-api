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
        self::$client->request(
            "DELETE",
            "/api/books/{$this->getLastBookId()}",
            [],
            [],
            self::$header
        );

        self::$client->request(
            "DELETE",
            "/api/orders/{$this->getLastOrderId()}",
            [],
            [],
            self::$header
        );
    }
}