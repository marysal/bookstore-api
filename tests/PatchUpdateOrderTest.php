<?php

use App\Tests\BaseTest;
use Symfony\Component\HttpFoundation\Response;

class PatchUpdateOrderTest extends BaseTest
{
    protected static $orderDataForUpdate = '[
        {"value":"newemail@newemail.ru","op":"replace","path":"/email"},
        {"value":"+375292571280","op":"replace","path":"/phone"},
        {"value":"dddddddddddddddddddddddddddddddddddddddddddd","op":"replace","path":"/address"}
    ]';

    /**
     * @dataProvider orderUpdateDataProvider
     */
    public function testPatchUpdate($email, $phone, $address)
    {
        self::$client->request(
            "PATCH",
            "/api/orders/{$this->getLastOrderId()}",
            [],
            [],
            self::$header,
            json_encode(self::$orderDataForUpdate)
        );

        $changedContent = json_decode(self::$client->getResponse()->getContent(), true);

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($changedContent);
        $this->assertArrayHasKey("id", $changedContent['data']);
        $this->assertArrayHasKey("email", $changedContent['data']);
        $this->assertSame($email, $changedContent['data']['email']);
        $this->assertArrayHasKey("phone", $changedContent['data']);
        $this->assertSame($phone, $changedContent['data']['phone']);
        $this->assertArrayHasKey("address", $changedContent['data']);
        $this->assertSame($address, $changedContent['data']['address']);
    }

    /**
     * @dataProvider orderUpdateUnsuccessfulDataProvider
     */
    public function testUpdateUnsuccessful(
        $jsonPatch,
        $responseCode,
        $message
    ) {
        self::$client->request(
            "PATCH",
            "/api/orders/{$this->getLastOrderId()}",
            [],
            [],
            self::$header,
            json_encode($jsonPatch)
        );

        $content = json_decode(self::$client->getResponse()->getContent(), true);

        $this->assertSame($responseCode, self::$client->getResponse()->getStatusCode());

        if($responseCode != Response::HTTP_OK) {
            $this->assertArrayHasKey("message", $content);
            $this->assertSame($message, $content["message"]);
        }

    }

    public function orderUpdateDataProvider()
    {
        return [
            [
                "email" => "newemail@newemail.ru",
                "phone" => "+375292571280",
                "address" => "dddddddddddddddddddddddddddddddddddddddddddd"
            ]
        ];
    }

    public function orderUpdateUnsuccessfulDataProvider()
    {
        return [
            [
                "jsonPatch" => '[
                   {"value":"newemail","op":"replace","path":"/email"},
                   {"value":"+375292571280","op":"replace","path":"/phone"},
                   {"value":"dddddddddddddddddddddddddddddddddddddddddddd","op":"replace","path":"/address"}
                ]',
                "responseCode" => Response::HTTP_BAD_REQUEST,
                "message" => "email: This value is not a valid email address."
            ],
            [
                "jsonPatch" => '[
                   {"value":"newemail@newemail.com","op":"replace","path":"/email"},
                   {"value":"+37529","op":"replace","path":"/phone"},
                   {"value":"dddddddddddddddddddddddddddddddddddddddddddd","op":"replace","path":"/address"}
                ]',
                "responseCode" => Response::HTTP_BAD_REQUEST,
                "message" => "phone: Invalid phone number"
            ],
            [
                "jsonPatch" => '[
                   {"value":"newemail@newemail.com","op":"replace","path":"/email"},
                   {"value":"+375292571280","op":"replace","path":"/phone"},
                   {"value":"dd","op":"replace","path":"/address"}
                ]',
                "responseCode" => Response::HTTP_BAD_REQUEST,
                "message" => "address: This value is too short. It should have 5 characters or more."
            ],
        ];
    }

}