<?php

use Symfony\Component\HttpFoundation\Response;

class UpdateBookTest extends BooksTest
{
    /**
     * @dataProvider bookUpdateDataProvider
     */
    public function testUpdate($book)
    {
        self::$client->request(
            "PUT",
            "/api/books/{$this->getLastBookId()}",
            $book,
            [],
            self::$header,
            json_encode($book)
        );

        $changedContent = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($changedContent);
        $this->assertArrayHasKey("id", $changedContent['data']);
        $this->assertArrayHasKey("title", $changedContent['data']);
        $this->assertSame("Changed title", $changedContent['data']['title']);
        $this->assertArrayHasKey("description", $changedContent['data']);
        $this->assertSame("Changed description", $changedContent['data']['description']);
        $this->assertArrayHasKey("type", $changedContent['data']);
        $this->assertSame("prose", $changedContent['data']['type']);
    }

    public function bookUpdateDataProvider()
    {
        return [
            [
                [
                    "title" => "Changed title",
                    "description" => "Changed description",
                    "type" => "prose"
                ]
            ]
        ];
    }
}