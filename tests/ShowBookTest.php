<?php

use Symfony\Component\HttpFoundation\Response;

class ShowBookTest extends BooksTest
{
    public function testShow()
    {
        self::$client->request(
            "GET",
            "/api/books/{$this->getLastBookId()}",
            [],
            [],
            self::$header
        );

        $content = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey("id", $content['data']);
        $this->assertSame($this->getLastBookId(), $content['data']['id']);
    }
}