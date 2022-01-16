<?php

use App\Entity\Author;
use App\Repository\AuthorRepository;

class Books extends BaseTest
{
    protected static $bookDataForUpdate =  [
        "title" => "Changed title",
        "description" => "Changed description",
        "type" => "prose"
    ];

    protected static $authorId;

    protected $book;

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
}