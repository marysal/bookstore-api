<?php

use App\Entity\Book;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseTest extends WebTestCase
{
    protected static $singleAuthor =   [
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

    protected function setUp(): void
    {
        parent::setUp();
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
        self::$singleBook["authors"] = [$this->getLastAuthorId()];

        self::$client->request(
            "POST",
            "/api/books/create",
            self::$singleBook,
            [],
            self::$header,
            json_encode(self::$singleBook)
        );

        //var_dump(json_decode(json_decode(self::$client->getResponse()->getContent()), true));

        $this->book = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

        $this->lastBookId = $this->book['data']['id'];

        //var_dump( $this->lastBookId);
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
        self::$singleOrder["books"] = [$this->getLastBookId()];

        self::$client->request(
            "POST",
            "/api/orders/create",
            self::$singleOrder,
            [],
            self::$header,
            json_encode(self::$singleOrder)
        );

        $this->order = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

        $this->lastOrderId = $this->order['data']['id'];
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
        self::$client->request(
            "DELETE",
            "/api/books/{$this->getLastBookId()}",
            [],
            [],
            self::$header
        );

        self::$client->request(
            "DELETE",
            "/api/authors/{$this->getLastAuthorId()}",
            [],
            [],
            self::$header
        );

        self::$client->request(
            "DELETE",
            "/api/orders/{$this->getLastOrderId()}",
            [],
            [],
            self::$header
        );
    }
}