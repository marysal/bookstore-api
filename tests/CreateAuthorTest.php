<?php

use Symfony\Component\HttpFoundation\Response;

class CreateAuthorTest extends BaseTest
{
    /**
     * @dataProvider authorDataProvider
     */
    public function testCreate($name)
    {
        self::$client->request(
            "POST",
            "/api/authors/create",
            self::$singleAuthor,
            [],
            self::$header,
            json_encode(self::$singleAuthor)
        );

        $content = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_CREATED, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey("id", $content['data']);
        $this->assertArrayHasKey("name", $content['data']);
        $this->assertSame($name, $content['data']['name']);

        $this->lastAuthorId = $content['data']['id'];
    }

    /**
     * @dataProvider authorUnsuccessfulDataProvider
     */
    public function testCreateUnsuccessful(
        $withToken,
        $responseCode,
        $message
    ) {


        self::$client->request("POST",
            "/api/authors/create",
            self::$singleAuthor,
            [],
            ($withToken) ? self::$header : [],
            json_encode(self::$singleAuthor)
        );

        $content = json_decode(self::$client->getResponse()->getContent(), true);

        $this->assertSame($responseCode, self::$client->getResponse()->getStatusCode());

        if($responseCode != Response::HTTP_CREATED) {
            $this->assertArrayHasKey("message", $content);
            $this->assertSame($message, $content["message"]);
        }
    }

    public function authorDataProvider()
    {
        return [
            [
                "name" => "Fedor Dostojevskij"
            ]
        ];
    }

    public function authorUnsuccessfulDataProvider()
    {
        return [
            [
                "withToken" => true,
                "responseCode" => Response::HTTP_CREATED,
                "message" => ""
            ],
            [
                "withToken" => false,
                "responseCode" => Response::HTTP_UNAUTHORIZED,
                "message" => "JWT Token not found"
            ]
        ];
    }

    protected function tearDown(): void
    {
        $query = $this->em->createQuery(
            'DELETE FROM App\Entity\Author a
             WHERE a.id = :id'
        )
        ->setParameter('id', $this->getLastAuthorId());

        $query->execute();
    }

}