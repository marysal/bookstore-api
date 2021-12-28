<?php

use Symfony\Component\HttpFoundation\Response;

class ListOrdersTest extends BaseTest
{
    public function testList()
    {
        self::$client->request(
            "GET",
            "/api/orders"
        );

        $this->assertSame(
            Response::HTTP_OK,
            self::$client->getResponse()->getStatusCode()
        );
    }
}