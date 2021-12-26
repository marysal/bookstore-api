<?php

use Symfony\Component\HttpFoundation\Response;

class UpdateBookTest extends BooksTest
{
    /**
     * @dataProvider bookUpdateDataProvider
     */
    public function testUpdate($book)
    {
        $this->client->request(
            "PUT",
            "/api/books/{$this->lastBookId}",
            $book,
            [],
            self::$header,
            json_encode($book)
        );

        $changedContent = json_decode(json_decode($this->client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertNotEmpty($changedContent);
        $this->assertArrayHasKey("id", $changedContent['data']);
        $this->assertArrayHasKey("title", $changedContent['data']);
        $this->assertSame("Changed title", $changedContent['data']['title']);
        $this->assertArrayHasKey("description", $changedContent['data']);
        $this->assertSame("Changed description", $changedContent['data']['description']);
        $this->assertArrayHasKey("type", $changedContent['data']);
        $this->assertSame("prose", $changedContent['data']['type']);
    }
}