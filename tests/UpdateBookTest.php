<?php

use Symfony\Component\HttpFoundation\Response;

class UpdateBookTest extends BooksTest
{
    /**
     * @dataProvider bookUpdateDataProvider
     */
    public function testUpdate($title, $description, $type)
    {
        self::$client->request(
            "PUT",
            "/api/books/{$this->getLastBookId()}",
            self::$bookDataForUpdate,
            [],
            self::$header,
            json_encode(self::$bookDataForUpdate)
        );

        $changedContent = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($changedContent);
        $this->assertArrayHasKey("id", $changedContent['data']);
        $this->assertArrayHasKey("title", $changedContent['data']);
        $this->assertSame($title, $changedContent['data']['title']);
        $this->assertArrayHasKey("description", $changedContent['data']);
        $this->assertSame($description, $changedContent['data']['description']);
        $this->assertArrayHasKey("type", $changedContent['data']);
        $this->assertSame($type, $changedContent['data']['type']);
    }

    public function bookUpdateDataProvider()
    {
        return [
            [
                "title" => "Changed title",
                "description" => "Changed description",
                "type" => "prose"
            ]
        ];
    }
}