<?php

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;

class BooksTest extends BaseTest
{
    protected static $bookDataForUpdate =  [
        "title" => "Changed title",
        "description" => "Changed description",
        "type" => "prose"
    ];

    protected static $authorId;

    protected $book;

    //protected $booksCount;

   /* protected function setUp(): void
    {
        parent::setUp();
        $this->setBooksCount();
    }*/

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::setAuthorId();
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
     * @return int
     */
   /* public function getBooksCount(): int
    {
        return $this->booksCount;
    }

    private function setBooksCount()
    {

        $book = self::$client->getContainer()->get('doctrine')->getRepository(Book::class);
        $this->booksCount = $book->createQueryBuilder('t')
            ->select('count(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }*/
}