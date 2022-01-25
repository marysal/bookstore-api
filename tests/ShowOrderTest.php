<?php

use App\Tests\BaseTest;
use Symfony\Component\HttpFoundation\Response;

class ShowOrderTest extends BaseTest
{
    public function testShow()
    {
        self::$client->request(
            "GET",
            "/api/orders/{$this->getLastOrderId()}",
            [],
            [],
            self::$header
        );

        $content = json_decode(self::$client->getResponse()->getContent(), true);

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey("id", $content['data']);
        $this->assertSame($this->getLastOrderId(), $content['data']['id']);
    }
}