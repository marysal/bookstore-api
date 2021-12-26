<?php

use Symfony\Component\HttpFoundation\Response;

class BookTestController extends BooksTest
{
    public function testList()
    {
        $this->client->request(
            "GET",
            "/api/books"
        );

        $this->assertSame(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }

    public function Update()
    {
        $body = [];
        $body["title"] = "Changed title";
        $body["description"] = "Changed description";
        $body["type"] = "prose";

        $this->client->request(
            "PUT",
            "/api/books/{$this->getBookId()}",
            $body,
            [],
            self::$header
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

        $body = [];
        $body["title"] = "New title";
        $body["description"] = "New description";
        $body["type"] = "poetry";

        $this->client->request(
            "PUT",
            "/api/books/{$this->getBookId()}",
            $body,
            [],
            self::$header
        );

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }


}