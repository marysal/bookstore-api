<?php

use Symfony\Component\HttpFoundation\Response;

class ListAuthorTest extends BaseTest
{
    public function testList()
    {
        self::$client->request(
            "GET",
            "/api/authors"
        );

        $this->assertSame(
            Response::HTTP_OK,
            self::$client->getResponse()->getStatusCode()
        );
    }
}