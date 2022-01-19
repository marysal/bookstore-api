<?php

use Symfony\Component\HttpFoundation\Response;

class ElasticSearchTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        self::$singleBook["authors"] = [$this->getLastAuthorId()];

        self::$client->request(
            "POST",
            "/api/books",
            self::$singleBook,
            [],
            self::$header,
            json_encode(self::$singleBook)
        );


    }

    protected static $singleBook = [
        "title" => "Elastic title",
        "description" => "Elastic description",
        "type" => "poetry"
    ];

    /**
     * @dataProvider bookDataProvider
     */
    public function testSearch($searchParams, $expectedResponseData) {

        //var_dump($expectedResponseData);die();

        self::$client->request(
            "GET",
            "/api/search",
            $expectedResponseData["searchParamsSuccess"],
            [],
            self::$header,
            json_encode( $expectedResponseData["searchParamsSuccess"])
        );

        $content = json_decode(self::$client->getResponse()->getContent(), true);

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        $this->assertSame($searchParams["title"], $content[0]['title']);
        $this->assertSame($searchParams["description"], $content[0]['description']);
        $this->assertSame($searchParams["type"], $content[0]['type']);
        $this->assertSame($searchParams["author"], $content[0]['authors'][0]["name"]);

        self::$client->request(
            "GET",
            "/api/search",
            $expectedResponseData["searchParamsUnsuccess"],
            [],
            self::$header,
            json_encode( $expectedResponseData["searchParamsUnsuccess"])
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, self::$client->getResponse()->getStatusCode());
    }

    public function bookDataProvider()
    {
        return [
            [
                "searchParams" => [
                    "title" => "Elastic title",
                    "description" => "Elastic description",
                    "type" => "poetry",
                    "author" => "Fedor Dostojevskij"
                ],
                "expectedResponseData" => [
                    "searchParamsSuccess" => [
                        "title" => "Elastic title"
                    ],
                    "searchParamsUnsuccess" => [
                        "title" => "Unsuccess Unsuccess"
                    ]
                ]
            ],
            [
                "searchParams" => [
                    "title" => "Elastic title",
                    "description" => "Elastic description",
                    "type" => "poetry",
                    "author" => "Fedor Dostojevskij",
                ],
                "expectedResponseData" => [
                    "searchParamsSuccess" => [
                        "description" => "Elastic description"
                    ],
                    "searchParamsUnsuccess" => [
                        "description" => "Unsuccess Unsuccess"
                    ]
                ]
            ],
            [
                "searchParams" => [
                    "title" => "Elastic title",
                    "description" => "Elastic description",
                    "type" => "poetry",
                    "author" => "Fedor Dostojevskij",
                ],
                "expectedResponseData" => [
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