<?php

use Symfony\Component\HttpFoundation\Response;
use App\Enum\StatusesOrdersEnum;

class UpdateOrderTest extends BaseTest
{
    private static $orderDataForUpdate = [
        "phone" => "+375(29)257-12-34",
        "address" => "Slavgorod"
    ];

    /**
     * @dataProvider orderUpdateDataProvider
     */
    public function testUpdate(
        $phone,
        $address,
        $status,
        $withToken,
        $responseCode,
        $message
    ) {
        self::$orderDataForUpdate["books"] = [$this->getLastBookId()];
        self::$orderDataForUpdate["status"] = $status;

        $order = $this->getOrder();

        self::$client->request(
            "PUT",
            "/api/orders/{$this->getLastOrderId()}",
            self::$orderDataForUpdate,
            [],
            ($withToken) ? self::$header : [],
            json_encode(self::$orderDataForUpdate)
        );

        $this->assertSame($responseCode, self::$client->getResponse()->getStatusCode());
        $response = json_decode(self::$client->getResponse()->getContent(), true);

        if($responseCode == Response::HTTP_OK) {
            $changedOrder = json_decode($response, true);
            $this->assertNotEmpty($changedOrder);
            $this->assertSame($order->getId(), $changedOrder['data']['id']);
            $this->assertArrayHasKey("phone", $changedOrder['data']);
            $this->assertNotEquals($phone, $order->getPhone());
            $this->assertNotEquals($address, $order->getAddress());
        } else {
            $this->assertArrayHasKey("message", $response);
            $this->assertSame($message, $response["message"]);
        }

    }

    public function orderUpdateDataProvider()
    {
        return [
            [
                "phone" => "+375(29)257-12-34",
                "address" => "Slavgorod",
                "status" => StatusesOrdersEnum::STATUS_DELIVERED,
                "withToken" => true,
                "responseCode" => Response::HTTP_OK,
                "message" => ""
            ],
            [
                "phone" => "+375(29)257-12-34",
                "address" => "Slavgorod",
                "status" => StatusesOrdersEnum::STATUS_DELIVERED,
                "withToken" => false,
                "responseCode" => Response::HTTP_UNAUTHORIZED,
                "message" => "Only admin can change order status."
            ]
        ];
    }
}