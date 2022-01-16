<?php

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Order;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseTest extends WebTestCase
{
    protected static $singleAuthor = [
        "name" => "Fedor Dostojevskij"
    ];

    protected static $singleBook = [
        "title" => "New title",
        "description" => "New description",
        "type" => "poetry"
    ];

    protected static $singleOrder = [
        "phone" => "+375(29)257-12-33",
        "address" => "Minsk, Leonardo Da Vinche str."
    ];

    protected static $bookId;

    protected static $client;

    protected static $header;

    protected static $token;

    protected $author;

    protected $order;

    protected $lastAuthorId;

    protected $lastBookId;

    protected $lastOrderId;

    /**
     * @var $em \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var $serializer \Symfony\Component\Serializer\Serializer
     */
    protected $serializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = self::$client->getContainer()
            ->get("doctrine.orm.entity_manager");

        $this->serializer = self::$client->getContainer()
            ->get("serializer");

        $this->setAuthor();
        $this->setBook();
        $this->setOrder();
    }

    public static function setUpBeforeClass(): void
    {
        self::ensureKernelShutdown();
        self::$client = static::createClient([]);
        static::setToken();
        self::setBookId();
    }

    private static function setToken(): void
    {
        self::$client->request(
            "POST",
            "/api/auth/login",
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            json_encode(["username" => "admin@admin.admin", "password" => "123456"])
        );

        $content = json_decode(self::$client->getResponse()->getContent());

        self::$token = $content->token;
        self::$header = [
            'HTTP_Authorization' => sprintf('%s %s', 'Bearer',  self::$token),
            'HTTP_CONTENT_TYPE' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT'       => 'application/json'
        ];
    }

    /**
     * @return int
     */
    public function getLastAuthorId(): int
    {
        return $this->lastAuthorId;
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
        /**
         * @var $author Author
         */
        $author = $this->serializer->deserialize(
            json_encode(self::$singleAuthor),
            Author::class,
            'json'
        );

        $this->em->persist($author);
        $this->em->flush();

        $this->author = $author;
        $this->lastAuthorId = $author->getId();
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
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
        /**
         * @var $book Book
         */
        $book = $this->serializer->deserialize(json_encode(self::$singleBook), Book::class, 'json');
        $book->appendAuthor($this->getAuthor());
        $this->em->persist($book);
        $this->em->flush();

        $this->book = $book;
        $this->lastBookId = $book->getId();
    }

    /**
     * @return int
     */
    public function getLastOrderId(): int
    {
        return $this->lastOrderId;
    }

    private function setOrder()
    {
        /**
         * @var $order Order
         */
        $order = $this->serializer->deserialize(
            json_encode(self::$singleOrder),
            Order::class,
            'json'
        );
        $order->appendBookOrderList($this->getBook());

        $this->em->persist($order);
        $this->em->flush();

        $this->order = $order;
        $this->lastOrderId = $order->getId();
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

    protected function tearDown(): void
    {
        $this->em->createQuery(
            'DELETE FROM App\Entity\Book b
             WHERE b.id = :id'
        )
        ->setParameter('id', $this->getLastBookId())
        ->execute();

        $this->em->createQuery(
            'DELETE FROM App\Entity\Author a
             WHERE a.id = :id'
        )
        ->setParameter('id', $this->getLastAuthorId())
        ->execute();

        $this->em->createQuery(
            'DELETE FROM App\Entity\Order o
             WHERE o.id = :id'
        )
        ->setParameter('id', $this->getLastOrderId())
        ->execute();
    }
}