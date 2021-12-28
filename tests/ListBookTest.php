<?php

use Symfony\Component\HttpFoundation\Response;
use App\Service\PaginatorService;

class ListBookTest extends BooksTest
{
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

    public function testPaginate()
    {
        $countPage = ceil($this->getBooksCount() / PaginatorService::ITEMS_PER_PAGE);
        $countFullPage = floor($this->getBooksCount() / PaginatorService::ITEMS_PER_PAGE);
        $countLastPageEntries = $this->getBooksCount() - ($countFullPage * PaginatorService::ITEMS_PER_PAGE);

        if (empty($countLastPageEntries)) {
            $countLastPageEntries = PaginatorService::ITEMS_PER_PAGE;
        }

        self::$client->request(
            "GET",
            "/api/books?page=1"
        );
        $content = json_decode(json_decode(self::$client->getResponse()->getContent()), true);
        $this->assertCount(PaginatorService::ITEMS_PER_PAGE, $content['data']);


        self::$client->request(
            "GET",
            "/api/books?page={$countPage}"
        );
        $lastPageContent = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

        $this->assertCount($countLastPageEntries, $lastPageContent['data']);
    }
}