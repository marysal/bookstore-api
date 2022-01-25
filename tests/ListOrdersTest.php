<?php

use App\Tests\BaseTest;
use Symfony\Component\HttpFoundation\Response;

class ListOrdersTest extends BaseTest
{
    public function testList()
    {
        self::$client->request(
            "GET",
            "/api/orders",
            [],
            [],
            self::$header
        );

        $this->assertSame(
            Response::HTTP_OK,
            self::$client->getResponse()->getStatusCode()
        );
    }
}