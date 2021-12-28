<?php

use App\Enum\StatusesOrdersEnum;
use Symfony\Component\HttpFoundation\Response;

class CreateOrderTestTest extends BaseTest
{
    /**
     * @dataProvider orderDataProvider
     */
    public function testCreate($phone, $address, $status)
    {
        self::$singleOrder["books"] = [$this->getLastBookId()];

        self::$client->request(
            'POST',
            '/api/orders/create',
            self::$singleOrder,
            [],
            self::$header,
            json_encode(self::$singleOrder)
        );

        $content = json_decode(json_decode(self::$client->getResponse()->getContent(), true), true);

        $this->assertSame(Response::HTTP_CREATED, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertSame($address, $content["data"]["address"]);
        $this->assertSame($phone, $content["data"]["phone"]);
        $this->assertSame($status, $content["data"]["status"]);
    }

    public function orderDataProvider()
    {
        return [
            [
                "phone" => "+375(29)257-12-33",
                "address" => "Minsk, Leonardo Da Vinche str.",
                "status" => StatusesOrdersEnum::STATUS_PENDING
            ]
        ];
    }
}