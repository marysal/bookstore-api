<?php

use Symfony\Component\HttpFoundation\Response;

class CreateBookXMLTest extends BookXmlTest
{
    /**
     * @dataProvider bookDataProvider
     */
    public function testCreate($title, $description, $type)
    {
        $xml = simplexml_load_string(self::$singleXMLBook);
        $xml->authors = null;

        foreach ([$this->getLastAuthorId()] as $id) {
            $xml->authors->id[] = $id;
        }

        self::$client->request(
            "POST",
            "/api/books",
            [],
            [],
            self::$header,
            $xml->asXML()
        );

        $content = simplexml_load_string(self::$client->getResponse()->getContent());
        $content = $this->object2array($content);

        $this->assertSame(Response::HTTP_CREATED, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey("id", $content['data']);
        $this->assertArrayHasKey("title", $content['data']);
        $this->assertSame($title, $content['data']['title']);
        $this->assertArrayHasKey("description", $content['data']);
        $this->assertSame($description, $content['data']['description']);
        $this->assertArrayHasKey("authors", $content['data']);
        $this->assertArrayHasKey("type", $content['data']);
        $this->assertSame($type, $content['data']['type']);
        $this->assertSame($this->getLastAuthorId(), (int)$content['data']['authors']['id']);
        $this->assertArrayNotHasKey("message", $content['data']);
        $this->assertArrayNotHasKey("error", $content['data']);

        $this->lastBookId = $content['data']['id'];
    }

    /**
     * @dataProvider bookUnsuccessfulDataProvider
     */
    public function testCreateUnsuccessful(
        $withAuthor,
        $withToken,
        $withNonExistentAuthor,
        $responseCode,
        $message
    ) {
        $xml = simplexml_load_string(self::$singleXMLBook);
        $xml->authors = null;

        if($withAuthor) {
            foreach ([$this->getLastAuthorId()] as $id) {
                $xml->authors->id[] = $id;
            }
        } elseif ($withNonExistentAuthor) {
            foreach ([$this->getLastAuthorId()] as $id) {
                $xml->authors->id[] = $id + 9999;
            }
        }

        self::$client->request(
            "POST",
            "/api/books",
            [],
            [],
            ($withToken) ? self::$header : [],
            $xml->asXML()
        );

        $content = json_decode(self::$client->getResponse()->getContent(), true);

        $this->assertSame($responseCode, self::$client->getResponse()->getStatusCode());


        if($responseCode != Response::HTTP_CREATED) {
            $this->assertArrayHasKey("message", $content);
            $this->assertSame($message, $content["message"]);
        }
    }

    public function bookDataProvider()
    {
        return [
            [
                "title" => "New title",
                "description" => "New description",
                "type" => "poetry"
            ]
        ];
    }

    public function bookUnsuccessfulDataProvider()
    {
        return [
            [
                "withAuthor" => true,
                "withToken" => true,
                "withNonExistentAuthor" => false,
                "responseCode" => Response::HTTP_CREATED,
                "message" => ""
            ],
            [
                "withAuthor" => true,
                "withToken" => false,
                "withNonExistentAuthor" => false,
                "responseCode" => Response::HTTP_UNAUTHORIZED,
                "message" => "JWT Token not found"
            ],
            [
                "withAuthor" => false,
                "withToken" => true,
                "withNonExistentAuthor" => false,
                "responseCode" => Response::HTTP_BAD_REQUEST,
                "message" => "The {book} must contain at least one relation ID"
            ],
            [
                "withAuthor" => false,
                "withToken" => true,
                "withNonExistentAuthor" => true,
                "responseCode" => Response::HTTP_BAD_REQUEST,
                "message" => "Object with this ID not found"
            ]
        ];
    }

    protected function tearDown(): void
    {
        $this->em->createQuery(
            'DELETE FROM App\Entity\Book b
             WHERE b.id = :id'
        )
            ->setParameter('id', $this->getLastBookId())
            ->execute();
    }
}