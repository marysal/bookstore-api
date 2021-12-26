<?php

use Symfony\Component\HttpFoundation\Response;

class ListBookTestController extends BooksTest
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
}