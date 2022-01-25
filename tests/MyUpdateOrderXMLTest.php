<?php

use App\Enum\StatusesOrdersEnum;
use Symfony\Component\HttpFoundation\Response;

class MyUpdateOrderXMLTest extends BaseXmlTest
{
    private static $orderDataForUpdate = "
        <orders>
            <phone>+375(29)257-12-34</phone>
            <address>Slavgorod</address>
        </orders>
    ";

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
        $xml = simplexml_load_string(self::$orderDataForUpdate);
        $xml->status = $status;

        foreach ([$this->getLastBookId()] as $id) {
            $xml->books->id[] = $id;
        }

        self::$client->request(
            "PUT",
            "/api/orders/{$this->getLastOrderId()}",
            [],
            [],
            ($withToken) ? self::$header : [],
            $xml->asXML()
        );

        $order = $this->getOrder();

        $response = simplexml_load_string(self::$client->getResponse()->getContent());
        $response = $this->object2array($response);

        $this->assertSame($responseCode, self::$client->getResponse()->getStatusCode());

        if($responseCode == Response::HTTP_OK) {
            $this->assertNotEmpty($response);
            $this->assertSame($order->getId(), (int)$response['data']['id']);
            $this->assertArrayHasKey("phone", $response['data']);
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