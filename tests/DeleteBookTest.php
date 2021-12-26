<?php

use Symfony\Component\HttpFoundation\Response;

class DeleteBookTest extends BooksTest
{
    public function testDestroy()
    {
        $this->client->request(
            "DELETE",
            "/api/books/{$this->lastBookId}",
            [],
            [],
            self::$header
        );

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
}