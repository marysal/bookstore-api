<?php

use Symfony\Component\HttpFoundation\Response;

class UpdateBookTest extends BooksTest
{
    /**
     * @dataProvider bookUpdateDataProvider
     */
    public function testUpdate($title, $description, $type)
    {
        self::$bookDataForUpdate["authors"] = [$this->getLastAuthorId()];

        self::$client->request(
            "PUT",
            "/api/books/{$this->getLastBookId()}",
            self::$bookDataForUpdate,
            [],
            self::$header,
            json_encode(self::$bookDataForUpdate)
        );

        $changedContent = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

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
        if($withAuthor) {
            self::$bookDataForUpdate["authors"] = [$this->getLastAuthorId()];
        } elseif ($withNonExistentAuthor) {
            self::$bookDataForUpdate["authors"] = [$this->getLastAuthorId() + 9999];
        } else {
            unset(self::$bookDataForUpdate["authors"]);
        }

        self::$client->request(
            "PUT",
            "/api/books/{$this->getLastBookId()}",
            self::$bookDataForUpdate,
            [],
            ($withToken) ? self::$header : [],
            json_encode(self::$bookDataForUpdate)
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
                "message" => "The book must contain at least one author ID"
            ],
            [
                "withAuthor" => false,
                "withToken" => true,
                "withNonExistentAuthor" => true,
                "responseCode" => Response::HTTP_NOT_FOUND,
                "message" => "Object with this ID not found"
            ]
        ];
    }
}