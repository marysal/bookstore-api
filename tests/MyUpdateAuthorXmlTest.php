<?php

use Symfony\Component\HttpFoundation\Response;

class MyUpdateAuthorXmlTest extends BaseXmlTest
{
    /**
     * @dataProvider authorUpdateDataProvider
     */
    public function testUpdate($name)
    {
        $xml = simplexml_load_string(self::$singleXMLAuthor);

        self::$client->request(
            "PUT",
            "/api/authors/{$this->getLastAuthorId()}",
            [],
            [],
            self::$header,
            $xml->asXML()
        );

        $changedContent = simplexml_load_string(self::$client->getResponse()->getContent());
        $changedContent = $this->object2array($changedContent);

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($changedContent);
        $this->assertArrayHasKey("name", $changedContent['data']);
        $this->assertNotEquals($name, $changedContent['data']['name']);
        $this->assertSame($this->getLastAuthorId(), (int)$changedContent['data']['id']);
    }

    public function authorUpdateDataProvider()
    {
        return [
            [
                "name" => "Updated author",
                "responseStatus" => Response::HTTP_OK
            ]
        ];
    }
}