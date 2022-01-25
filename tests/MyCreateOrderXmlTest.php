<?php

use App\Enum\StatusesOrdersEnum;
use Symfony\Component\HttpFoundation\Response;

class MyCreateOrderXmlTest extends BaseXmlTest
{
    /**
     * @dataProvider orderDataProvider
     */
    public function testCreate($phone, $address, $status, $countBooks)
    {
        $xml = simplexml_load_string(self::$singleXMLOrder);
        $xml->books = null;

        for ($i = 0; $i < $countBooks; $i++) {
            $xml->books->id[] = $this->getLastBookId() - $i;
        }

        self::$client->request(
            'POST',
            '/api/orders',
            [],
            [],
            self::$header,
            $xml->asXML()
        );

        $content = simplexml_load_string(self::$client->getResponse()->getContent());
        $content = $this->object2array($content);

        $this->assertSame(Response::HTTP_CREATED, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertSame($address, $content["data"]["address"]);
        $this->assertSame($phone, $content["data"]["phone"]);
        $this->assertSame($status, $content["data"]["status"]);
        //$this->assertCount($countBooks, $content["data"]["bookOrderList"]);

        $this->lastOrderId = $content['data']['id'];
    }

    public function orderDataProvider()
    {
        return [
            [
                "phone" => "+375(29)257-12-33",
                "address" => "Minsk, Leonardo Da Vinche str.",
                "status" => StatusesOrdersEnum::STATUS_PENDING,
                "countBooks" =>  1
            ],
        ];
    }
}