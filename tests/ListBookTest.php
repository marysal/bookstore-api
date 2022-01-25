<?php

use App\Entity\Book;
use App\Tests\Books;
use Symfony\Component\HttpFoundation\Response;

class ListBookTest extends Books
{
    protected function setUp(): void
    {
        parent::setUp();

        self::$client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->createQuery("DELETE App\Entity\Book b")
            ->execute();

        foreach (range(1, 11) as $row) {
            /**
             * @var $book Book
             */
            $book = $this->serializer->deserialize(json_encode(self::$singleBook), Book::class, 'json');
            $book->appendAuthor($this->getAuthor());
            $this->em->persist($book);
        }
        $this->em->flush();
    }

    public function testList()
    {
        self::$client->request(
            "GET",
            "/api/books",
            [],
            [],
            self::$header
        );

        $this->assertSame(
            Response::HTTP_OK,
            self::$client->getResponse()->getStatusCode()
        );
    }

    /**
     * @group paginate
     * Tests the api edit form
     * @dataProvider paginatorDataProvider
     */
    public function testPaginate($page, $count)
    {
        self::$client->request(
            "GET",
            "/api/books?page={$page}",
            [],
            [],
            self::$header,
        );
        $content = json_decode(self::$client->getResponse()->getContent(), true);

        $this->assertCount($count, $content['data']);
    }

    public function paginatorDataProvider()
    {
        return [
            [
                "page" => 1,
                "count" => 3
            ],
            [
                "page" => 4,
                "count" => 2
            ]
        ];
    }
}