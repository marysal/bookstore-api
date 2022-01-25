<?php

use App\Tests\Books;
use Symfony\Component\HttpFoundation\Response;

class PatchUpdateBookTest extends Books
{
    protected static $bookDataForUpdate = '[
        {"value":"Patch updated title","op":"replace","path":"/title"},
        {"value":"Patch updated description","op":"replace","path":"/description"},
        {"value":"poetry","op":"replace","path":"/type"}
    ]';

    /**
     * @dataProvider bookUpdateDataProvider
     */
    public function testPatchUpdate($title, $description)
    {
        self::$client->request(
            "PATCH",
            "/api/books/{$this->getLastBookId()}",
            [],
            [],
            self::$header,
            json_encode(self::$bookDataForUpdate)
        );

        $changedContent = json_decode(self::$client->getResponse()->getContent(), true);

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($changedContent);
        $this->assertArrayHasKey("id", $changedContent['data']);
        $this->assertArrayHasKey("title", $changedContent['data']);
        $this->assertSame($title, $changedContent['data']['title']);
        $this->assertArrayHasKey("description", $changedContent['data']);
        $this->assertSame($description, $changedContent['data']['description']);
    }

    public function bookUpdateDataProvider()
    {
        return [
            [
                "title" => "Patch updated title",
                "description" => "Patch updated description"
            ]
        ];
    }

    /**
     * @dataProvider bookUpdateUnsuccessfulDataProvider
     */
    public function testUpdateUnsuccessful(
        $jsonPatch,
        $responseCode,
        $message
    ) {
        self::$client->request(
            "PATCH",
            "/api/books/{$this->getLastBookId()}",
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

    public function bookUpdateUnsuccessfulDataProvider()
    {
        return [
            [
               "jsonPatch" => '[
                   {"value":"Pa","op":"replace","path":"/title"},
                   {"value":"Patch updated description","op":"replace","path":"/description"}
                ]',
               "responseCode" => Response::HTTP_BAD_REQUEST,
               "message" => "title: This value is too short. It should have 5 characters or more."
            ],
            [
                "jsonPatch" => '[
                    {"value":"Patch updated title","op":"replace","path":"/title"},
                    {"value":"new type","op":"replace","path":"/type"}
                ]',
                "responseCode" => Response::HTTP_BAD_REQUEST,
                "message" => "type: You can choose 'prose' or 'poetry'"
            ],
        ];
    }
}