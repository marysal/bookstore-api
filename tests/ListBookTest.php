<?php

use App\Entity\Book;
use App\Service\PaginatorService;
use Symfony\Component\HttpFoundation\Response;

class ListBookTest extends BooksTest
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
            "/api/books"
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
    public function testPaginate($lastPage, $countLastPageBooks)
    {
        self::$client->request(
            "GET",
            "/api/books?page=1"
        );
        $content = json_decode(json_decode(self::$client->getResponse()->getContent()), true);
        $this->assertCount(PaginatorService::ITEMS_PER_PAGE, $content['data']);

        self::$client->request(
            "GET",
            "/api/books?page=4",
            ["page" => $lastPage]
        );

        $lastPageContent = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

        $this->assertCount($countLastPageBooks, $lastPageContent['data']);
    }

    public function paginatorDataProvider()
    {
        return [
            [
                "lastPage" => 4,
                "countLastPageBooks" => 2
            ]
        ];
    }
}