<?php

use Symfony\Component\HttpFoundation\Response;

class ElasticSearchTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

    }

    protected static $singleBook = [
        "title" => "Elastic title",
        "description" => "Elastic description",
        "type" => "poetry"
    ];

    /**
     * @dataProvider bookDataProvider
     */
    public function testSearchSuccessful($title, $description, $type, $author, $searchParams)
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

        self::$client->request(
            "GET",
            "/api/search",
            $searchParams,
            [],
            self::$header,
            json_encode($searchParams)
        );

        $content = json_decode(self::$client->getResponse()->getContent(), true);

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        $this->assertSame($title, $content[0]['title']);
        $this->assertSame($description, $content[0]['description']);
        $this->assertSame($type, $content[0]['type']);
        $this->assertSame($author, $content[0]['authors'][0]["name"]);
    }

    public function bookDataProvider()
    {
        return [
            [
                "title" => "Elastic title",
                "description" => "Elastic description",
                "type" => "poetry",
                "author" => "Fedor Dostojevskij",
                "searchParams" => [
                    "title" => "Elastic title"
                ]
            ],
            [
                "title" => "Elastic title",
                "description" => "Elastic description",
                "type" => "poetry",
                "author" => "Fedor Dostojevskij",
                "searchParams" => [
                    "description" => "Elastic description"
                ]
            ],
            [
                "title" => "Elastic title",
                "description" => "Elastic description",
                "type" => "poetry",
                "author" => "Fedor Dostojevskij",
                "searchParams" => [
                    "title" => "Elastic title",
                    "description" => "Elastic description",
                    "authors.name" => "Fedor Dostojevskij"
                ]
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