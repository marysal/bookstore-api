<?php

use Symfony\Component\HttpFoundation\Response;

class UpdateAuthor extends BaseTest
{
    /**
     * @dataProvider authorUpdateDataProvider
     */
    public function testUpdate($name)
    {
        self::$client->request(
            "PUT",
            "/api/authors/{$this->getLastAuthorId()}",
            self::$singleAuthor,
            [],
            self::$header,
            json_encode(self::$singleAuthor)
        );

        $changedContent = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($changedContent);
        $this->assertArrayHasKey("name", $changedContent['data']);
        $this->assertNotEquals($name, $changedContent['data']['name']);
        $this->assertSame($this->getLastAuthorId(), $changedContent['data']['id']);
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