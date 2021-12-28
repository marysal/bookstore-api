<?php

use Symfony\Component\HttpFoundation\Response;

class DeleteOrderTest extends BaseTest
{
    public function testDestroy()
    {
        self::$client->request(
            "DELETE",
            "/api/orders/{$this->getLastOrderId()}",
            [],
            [],
            self::$header
        );

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
    }
}