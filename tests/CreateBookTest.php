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
    public function testCreate($title, $description, $type)
    {
        self::$singleBook["authors"] = [$this->getLastAuthorId()];

        self::$client->request(
            "POST",
            "/api/books/create",
            self::$singleBook,
            [],
            self::$header,
            json_encode(self::$singleBook)
        );

        $content = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_CREATED, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey("id", $content['data']);
        $this->assertArrayHasKey("title", $content['data']);
        $this->assertSame($title, $content['data']['title']);
        $this->assertArrayHasKey("description", $content['data']);
        $this->assertSame($description, $content['data']['description']);
        $this->assertArrayHasKey("authors", $content['data']);
        $this->assertArrayHasKey("type", $content['data']);
        $this->assertSame($type, $content['data']['type']);
        $this->assertSame($this->getLastAuthorId(), $content['data']['authors'][0]['id']);

        $this->lastBookId = $content['data']['id'];
    }

    public function bookDataProvider()
    {
        return [
            [
                "title" => "New title",
                "description" => "New description",
                "type" => "poetry"
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