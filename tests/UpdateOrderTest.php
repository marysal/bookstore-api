<?php

use Symfony\Component\HttpFoundation\Response;

class UpdateOrderTest extends BaseTest
{
    private static $orderDataForUpdate = [
        "phone" => "+375(29)257-12-34",
        "address" => "Slavgorod"
    ];

    /**
     * @dataProvider orderUpdateDataProvider
     */
    public function testUpdate($phone, $address)
    {
        self::$orderDataForUpdate["books"] = [$this->getLastBookId()];

        $order = $this->getOrder();

        self::$client->request(
            "PUT",
            "/api/orders/{$this->getLastOrderId()}",
            self::$orderDataForUpdate,
            [],
            self::$header,
            json_encode(self::$orderDataForUpdate)
        );

        $changedOrder = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($changedOrder);
        $this->assertSame($order['data']['id'], $changedOrder['data']['id']);
        $this->assertArrayHasKey("phone", $changedOrder['data']);
        $this->assertNotEquals($phone, $order['data']['phone']);
        $this->assertNotEquals($address, $order['data']['address']);
    }

    public function orderUpdateDataProvider()
    {
        return [
            [
                "phone" => "+375(29)257-12-34",
                "address" => "Slavgorod"
            ]
        ];
    }
}