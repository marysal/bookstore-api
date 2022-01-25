<?php

use Symfony\Component\HttpFoundation\Response;

class MyUpdateBookXMLTest extends MyBookXmlTest
{
    /**
     * @dataProvider bookUpdateDataProvider
     */
    public function testUpdate($title, $description, $type)
    {
        $xml = simplexml_load_string(self::$bookDataForUpdate);
        $xml->authors = null;

        foreach ([$this->getLastAuthorId()] as $id) {
            $xml->authors->id[] = $id;
        }

        self::$client->request(
            "PUT",
            "/api/books/{$this->getLastBookId()}",
            [],
            [],
            self::$header,
            $xml->asXML()
        );

        $changedContent = simplexml_load_string(self::$client->getResponse()->getContent());
        $changedContent = $this->object2array($changedContent);

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($changedContent);
        $this->assertArrayHasKey("id", $changedContent['data']);
        $this->assertArrayHasKey("title", $changedContent['data']);
        $this->assertSame($title, $changedContent['data']['title']);
        $this->assertArrayHasKey("description", $changedContent['data']);
        $this->assertSame($description, $changedContent['data']['description']);
        $this->assertArrayHasKey("type", $changedContent['data']);
        $this->assertSame($type, $changedContent['data']['type']);
    }

    /**
     * @dataProvider bookUpdateUnsuccessfulDataProvider
     */
    public function testUpdateUnsuccessful(
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
            "PUT",
            "/api/books/{$this->getLastBookId()}",
            [],
            [],
            ($withToken) ? self::$header : [],
            $xml->asXML()
        );

        $content = json_decode(self::$client->getResponse()->getContent(), true);

        $this->assertSame($responseCode, self::$client->getResponse()->getStatusCode());

        if($responseCode != Response::HTTP_OK) {
            $this->assertArrayHasKey("message", $content);
            $this->assertSame($message, $content["message"]);
        }
    }

    public function bookUpdateDataProvider()
    {
        return [
            [
                "title" => "Changed title",
                "description" => "Changed description",
                "type" => "prose"
            ]
        ];
    }

    public function bookUpdateUnsuccessfulDataProvider()
    {
        return [
            [
                "withAuthor" => true,
                "withToken" => true,
                "withNonExistentAuthor" => false,
                "responseCode" => Response::HTTP_OK,
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
}