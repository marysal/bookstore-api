<?php

use Symfony\Component\HttpFoundation\Response;

class MyCreateAuthorXmlTest extends BaseXmlTest
{
    /**
     * @dataProvider authorDataProvider
     */
    public function testCreate($name, $contentType)
    {
        $xml = simplexml_load_string(self::$singleXMLAuthor);

        self::$client->request(
            "POST",
            "/api/authors",
            [],
            [],
            self::$header,
            $xml->asXML()
        );

        $content = simplexml_load_string(self::$client->getResponse()->getContent());
        $content = $this->object2array($content);

        $this->assertSame(Response::HTTP_CREATED, self::$client->getResponse()->getStatusCode());
        $this->assertSame($contentType, self::$client->getResponse()->headers->get("content-type"));
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
        $message,
        $header
    ) {
        $header = empty($header) ? self::$header : $header;
        $xml = simplexml_load_string(self::$singleXMLAuthor);

        self::$client->request("POST",
            "/api/authors",
            [],
            [],
            ($withToken) ? $header : [],
            $xml->asXML()
        );

        $content = self::$client->getResponse()->getContent();
        $contentArr = json_decode($content, true);

        $this->assertSame($responseCode, self::$client->getResponse()->getStatusCode());

        if($responseCode != Response::HTTP_CREATED && is_array($contentArr)) {
            $this->assertArrayHasKey("message", $contentArr);
            $this->assertSame($message, $contentArr["message"]);
        } else if ($responseCode != Response::HTTP_CREATED && is_string($contentArr)) {
            $this->assertSame($message, $content);
        }
    }


    public function authorDataProvider()
    {
        return [
            [
                "name" => "Fedor Dostojevskij",
                "contentType" => "application/xml",
            ]
        ];
    }

    public function authorUnsuccessfulDataProvider()
    {
        return [
            [
                "withToken" => true,
                "responseCode" => Response::HTTP_CREATED,
                "message" => "",
                "header" => []
            ],
            [
                "withToken" => false,
                "responseCode" => Response::HTTP_UNAUTHORIZED,
                "message" => "JWT Token not found",
                "header" => []
            ],
            [
                "withToken" => true,
                "responseCode" => Response::HTTP_BAD_REQUEST,
                "message" => "Unable to parse request.",
                "header" =>  [
                    'HTTP_Authorization' => sprintf('%s %s', 'Bearer',  self::$token),
                    'HTTP_CONTENT_TYPE' => 'application/json',
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_ACCEPT'  => 'application/json'
                ]
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