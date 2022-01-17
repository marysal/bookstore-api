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
    public function testSearch(
        $title,
        $description,
        $type,
        $author,
        $searchParamsSuccess,
        $searchParamsUnsuccess
    ) {
        self::$singleBook["authors"] = [$this->getLastAuthorId()];

        self::$client->request(
            "POST",
            "/api/books",
            self::$singleBook,
            [],
            self::$header,
            json_encode(self::$singleBook)
        );

        self::$client->request(
            "GET",
            "/api/search",
            $searchParamsSuccess,
            [],
            self::$header,
            json_encode($searchParamsSuccess)
        );

        $content = json_decode(self::$client->getResponse()->getContent(), true);

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        $this->assertSame($title, $content[0]['title']);
        $this->assertSame($description, $content[0]['description']);
        $this->assertSame($type, $content[0]['type']);
        $this->assertSame($author, $content[0]['authors'][0]["name"]);

        self::$client->request(
            "GET",
            "/api/search",
            $searchParamsUnsuccess,
            [],
            self::$header,
            json_encode($searchParamsUnsuccess)
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, self::$client->getResponse()->getStatusCode());
    }

    public function bookDataProvider()
    {
        return [
            [
                "title" => "Elastic title",
                "description" => "Elastic description",
                "type" => "poetry",
                "author" => "Fedor Dostojevskij",
                "searchParamsSuccess" => [
                    "title" => "Elastic title"
                ],
                "searchParamsUnsuccess" => [
                    "title" => "Unsuccess Unsuccess"
                ]
            ],
            [
                "title" => "Elastic title",
                "description" => "Elastic description",
                "type" => "poetry",
                "author" => "Fedor Dostojevskij",
                "searchParamsSuccess" => [
                    "description" => "Elastic description"
                ],
                "searchParamsUnsuccess" => [
                    "description" => "Unsuccess Unsuccess"
                ]
            ],
            [
                "title" => "Elastic title",
                "description" => "Elastic description",
                "type" => "poetry",
                "author" => "Fedor Dostojevskij",
                "searchParamsSuccess" => [
                    "title" => "Elastic title",
                    "description" => "Elastic description",
                    "authors.name" => "Fedor Dostojevskij"
                ],
                "searchParamsUnsuccess" => [
                    "title" => "Unsuccess Unsuccess",
                    "description" => "Unsuccess Unsuccess"
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