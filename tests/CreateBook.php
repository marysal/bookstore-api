<?php

use Symfony\Component\HttpFoundation\Response;

class CreateBook extends Books
{
    /**
     * @dataProvider bookDataProvider
     */
    public function testCreate($title, $description, $type)
    {
        self::$singleBook["authors"] = [$this->getLastAuthorId()];

        self::$client->request(
            "POST",
            "/api/books/create",
            self::$singleBook,
            [],
            self::$header,
            json_encode(self::$singleBook)
        );

        $content = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

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
        $this->assertSame($this->getLastAuthorId(), $content['data']['authors'][0]['id']);
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
        if($withAuthor) {
            self::$singleBook["authors"] = [$this->getLastAuthorId()];
        } elseif ($withNonExistentAuthor) {
            self::$singleBook["authors"] = [$this->getLastAuthorId() + 9999];
        } else {
            unset(self::$singleBook["authors"]);
        }

        self::$client->request(
            "POST",
            "/api/books/create",
            self::$singleBook,
            [],
            ($withToken) ? self::$header : [],
            json_encode(self::$singleBook)
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