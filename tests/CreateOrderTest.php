<?php

use App\Enum\StatusesOrdersEnum;
use App\Tests\BaseTest;
use Symfony\Component\HttpFoundation\Response;

class CreateOrderTest extends BaseTest
{
    /**
     * @dataProvider orderDataProvider
     */
    public function testCreate($phone, $address, $status, $countBooks)
    {
        self::$singleOrder["books"] = [];

        for ($i = 0; $i < $countBooks; $i++) {
            self::$singleOrder["books"][$i] = $this->getLastBookId() - $i;
        }

        self::$client->request(
            'POST',
            '/api/orders',
            self::$singleOrder,
            [],
            self::$header,
            json_encode(self::$singleOrder)
        );

        $content = json_decode(self::$client->getResponse()->getContent(), true);

        $this->assertSame(Response::HTTP_CREATED, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertSame($address, $content["data"]["address"]);
        $this->assertSame($phone, $content["data"]["phone"]);
        $this->assertSame($status, $content["data"]["status"]);
        $this->assertCount($countBooks, $content["data"]["bookOrderList"]);

        $this->lastOrderId = $content['data']['id'];
    }

    /**
     * @dataProvider orderUnsuccessfulDataProvider
     */
    public function testCreateUnsuccessful(
        $phone,
        $withBook,
        $wrongBookId,
        $wrongStatus,
        $responseCode,
        $message
    ) {

        if($wrongBookId) {
            self::$singleOrder["books"] = [$this->getLastBookId() + 9999];
        } else {
            self::$singleOrder["books"] = ($withBook) ? [$this->getLastBookId()] : [];
        }

        if($wrongStatus) {
            self::$singleOrder["status"] = "fffffff";
        }

        self::$singleOrder["phone"] = $phone;

        self::$client->request(
            'POST',
            '/api/orders',
            self::$singleOrder,
            [],
            self::$header,
            json_encode(self::$singleOrder)
        );

        $content = json_decode(self::$client->getResponse()->getContent(), true);

        $this->assertSame($responseCode, self::$client->getResponse()->getStatusCode());

        if($responseCode != Response::HTTP_CREATED) {
            $this->assertArrayHasKey("message", $content);
            $this->assertSame($message, $content["message"]);
        }
    }

    public function orderDataProvider()
    {
        return [
            [
                "phone" => "+375(29)257-12-33",
                "address" => "Minsk, Leonardo Da Vinche str.",
                "status" => StatusesOrdersEnum::STATUS_PENDING,
                "countBooks" =>  1,
            ],
        ];
    }

    public function orderUnsuccessfulDataProvider()
    {
        return [
            [
                "phone" => "+375(29)257-11-33",
                "withBook" => true,
                "wrongBookId" => false,
                "wrongStatus" => false,
                "responseCode" => Response::HTTP_CREATED,
                "message" => ""
            ],
            [
                "phone" => "+375(29)257",
                "withBook" => true,
                "wrongBookId" => false,
                "wrongStatus" => false,
                "responseCode" => Response::HTTP_BAD_REQUEST,
                "message" => "phone: Invalid phone number"
            ],
            [
                "phone" => "+375(29)257-11-33",
                "withBook" => false,
                "wrongBookId" => false,
                "wrongStatus" => false,
                "responseCode" => Response::HTTP_BAD_REQUEST,
                "message" => "The {order} must contain at least one relation ID"
            ],
            [
                "phone" => "+375(29)257-11-33",
                "withBook" => false,
                "wrongBookId" => true,
                "wrongStatus" => false,
                "responseCode" => Response::HTTP_BAD_REQUEST,
                "message" => "Object with this ID not found"
            ],
            [
                "phone" => "+375(29)257-11-33",
                "withBook" => true,
                "wrongBookId" => false,
                "wrongStatus" => true,
                "responseCode" => Response::HTTP_BAD_REQUEST,
                "message" => "status: The value you selected is not a valid choice."
            ]
        ];
    }

    protected function tearDown(): void
    {
        $this->em->createQuery(
            'DELETE FROM App\Entity\Order o
             WHERE o.id = :id'
        )
        ->setParameter('id', $this->getLastOrderId())
        ->execute();
    }
}