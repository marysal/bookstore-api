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
}