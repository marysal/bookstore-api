<?php

use Symfony\Component\HttpFoundation\Response;

class CreateBookTest extends BooksTest
{
    public function testCreate()
    {
        /**
         * I use the provider in this way, because of it's initialized before the setUp()
         * method and the author's id doesn't have time to be installed
         */
        $book = $this->bookDataProvider();

        $this->client->request(
            "POST",
            "/api/books/create",
            $book,
            [],
            self::$header,
            json_encode($book)
        );

        $content = json_decode(json_decode($this->client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey("id", $content['data']);
        $this->assertArrayHasKey("title", $content['data']);
        $this->assertSame("New title", $content['data']['title']);
        $this->assertArrayHasKey("description", $content['data']);
        $this->assertSame("New description", $content['data']['description']);
        $this->assertArrayHasKey("authors", $content['data']);
        $this->assertSame($this->getAuthorId(), $content['data']['authors'][0]['id']);

        $this->lastBookId = $content['data']['id'];
    }

    protected function tearDown(): void
    {
        $this->client->request(
            "DELETE",
            "/api/books/{$this->lastBookId}",
            [],
            [],
            self::$header
        );
    }
}