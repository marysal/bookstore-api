<?php

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;

class BooksTest extends BaseTest
{
    protected $book;

    protected $authorId;

    protected $bookId;

    protected $lastBookId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setAuthorId();
        $this->setBookId();
        $this->setBook();
    }

    /**
     * @return mixed
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }

    public function setAuthorId(): void
    {
        $client = static::createClient([]);

        /** @var AuthorRepository $authorRepository */
        $authorRepository = $client->getContainer()->get('doctrine')->getRepository(Author::class);
        $authors = $authorRepository->findOne();
        $this->authorId = $authors[0]->getId();
    }

    public function bookDataProvider()
    {
        return [
            "title" => "New title",
            "description" => "New description",
            "type" => "poetry",
            "authors" => [$this->getAuthorId()]
        ];
    }

    public function bookUpdateDataProvider()
    {
        return [
            [
                [
                    "title" => "Changed title",
                    "description" => "Changed description",
                    "type" => "prose"
                ]
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function getBookId()
    {
        return $this->bookId;
    }

    public function setBookId()
    {
        $client = static::createClient([]);

        /** @var BookRepository $bookRepository */
        $bookRepository = $client->getContainer()->get('doctrine')->getRepository(Book::class);
        $books = $bookRepository->findOne();
        $this->bookId = $books[0]->getId();
    }

    /**
     * @return mixed
     */
    public function getBook()
    {
        return $this->book;
    }

    private function setBook()
    {
        /**
         * I use the provider in this way, because of it's initialized before the setUp()
         * method and the author's id doesn't have time to be installed
         */
        $book = $this->bookDataProvider();

         $this->client->request(
            "POST",
            "/api/books/create",
            $book,
            [],
            self::$header,
            json_encode($book)
        );

        $this->book = json_decode(json_decode($this->client->getResponse()->getContent()), true);

        $this->lastBookId = $this->book['data']['id'];
    }

    protected function tearDown(): void
    {
        $this->book = null;
    }
}