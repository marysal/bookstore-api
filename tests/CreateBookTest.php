<?php

use Symfony\Component\HttpFoundation\Response;

class CreateBookTest extends BooksTest
{
    protected function setUp(): void
    {
        $this->setAuthor();
    }

    /**
     * @dataProvider bookDataProvider
     */
    public function testCreate($book)
    {
        $book["authors"] = [$this->getLastAuthorId()];

        self::$client->request(
            "POST",
            "/api/books/create",
            $book,
            [],
            self::$header,
            json_encode($book)
        );

        $content = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_CREATED, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey("id", $content['data']);
        $this->assertArrayHasKey("title", $content['data']);
        $this->assertSame("New title", $content['data']['title']);
        $this->assertArrayHasKey("description", $content['data']);
        $this->assertSame("New description", $content['data']['description']);
        $this->assertArrayHasKey("authors", $content['data']);
        $this->assertSame($this->getLastAuthorId(), $content['data']['authors'][0]['id']);

        $this->lastBookId = $content['data']['id'];
    }

    public function bookDataProvider()
    {
        return [
            [
                [
                    "title" => "New title",
                    "description" => "New description",
                    "type" => "poetry"
                ]
            ]
        ];
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
    }
}