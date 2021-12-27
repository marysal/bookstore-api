<?php

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;

class BooksTest extends BaseTest
{
    protected static $authorId;

    protected static $bookId;

    protected $author;

    protected $book;

    protected $lastBookId;

    protected $lastAuthorId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setAuthor();
        $this->setBook();
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::setAuthorId();
        self::setBookId();
    }

    /**
     * @return mixed
     */
    public function getAuthorId()
    {
        return self::$authorId;
    }

    public static function setAuthorId(): void
    {
        /** @var AuthorRepository $authorRepository */
        $authorRepository = self::$client->getContainer()->get('doctrine')->getRepository(Author::class);
        $authors = $authorRepository->findOne();
        self::$authorId = $authors[0]->getId();
    }

    /**
     * @return mixed
     */
    public function getBookId()
    {
        return self::$bookId;
    }

    public static function setBookId()
    {
        /** @var BookRepository $bookRepository */
        $bookRepository = self::$client->getContainer()->get('doctrine')->getRepository(Book::class);
        $books = $bookRepository->findOne();
        self::$bookId = $books[0]->getId();
    }

    /**
     * @return mixed
     */
    public function getBook()
    {
        return $this->book;
    }

    private function setBook(): void
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

        $this->book = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

        $this->lastBookId = $this->book['data']['id'];
    }

    /**
     * @return int
     */
    public function getLastAuthorId(): int
    {
        return $this->lastAuthorId;
    }

    /**
     * @return int
     */
    public function getLastBookId(): int
    {
        return $this->lastBookId;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    protected function setAuthor()
    {
        self::$client->request(
            "POST",
            "/api/authors/create",
            self::$singleAuthor,
            [],
            self::$header,
            json_encode(self::$singleAuthor)
        );

        $this->author = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

        $this->lastAuthorId = $this->author['data']['id'];
    }

    protected function tearDown(): void
    {
        $this->book = null;
        $this->author = null;

        self::$client->request(
            "DELETE",
            "/api/authors/{$this->lastAuthorId}",
            [],
            [],
            self::$header
        );
        $this->lastBookId = null;
        self::$authorId = null;
    }
}