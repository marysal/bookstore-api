<?php

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\BookRepository;

class BookControllerTest extends WebTestCase
{
    private static $token;

    private static $header;

    public static function setUpBeforeClass(): void
    {
        static::setToken();
    }

    public function testGET()
    {
        $client = static::createClient([]);

        $client->request(
            "GET",
            "/api/books"
        );

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testCreate()
    {
        $client = static::createClient([]);

        /** @var AuthorRepository $authorRepository */
        $authorRepository = $client->getContainer()->get('doctrine')->getRepository(Author::class);
        $authors = $authorRepository->findOne();
        $authorId = $authors[0]->getId();
        $this->assertIsArray($authors);
        $this->assertNotEmpty($authorId);
        $this->assertIsInt($authorId);

        $body = [];
        $body["title"] = "New title";
        $body["description"] = "New description";
        $body["type"] = "poetry";
        $body["authors"] = [$authorId];

        $client->request(
            "POST",
            "/api/books/create",
            $body,
            [],
            self::$header
        );

        $content = json_decode(json_decode($client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey("id", $content['data']);
        $this->assertArrayHasKey("title", $content['data']);
        $this->assertSame("New title", $content['data']['title']);
        $this->assertArrayHasKey("description", $content['data']);
        $this->assertSame("New description", $content['data']['description']);
        $this->assertArrayHasKey("authors", $content['data']);
        $this->assertSame($authorId, $content['data']['authors'][0]['id']);

        $client->request(
            "DELETE",
            "/api/books/{$content['data']['id']}",
            $body,
            [],
            self::$header
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testUpdate()
    {
        $client = static::createClient([]);

        /** @var BookRepository $bookRepository */
        $bookRepository = $client->getContainer()->get('doctrine')->getRepository(Book::class);
        $books = $bookRepository->findOne();
        $bookId = $books[0]->getId();
        $this->assertIsArray($books);
        $this->assertNotEmpty($bookId);
        $this->assertIsInt($bookId);


        $body = [];
        $body["title"] = "Changed title";
        $body["description"] = "Changed description";
        $body["type"] = "prose";

        $client->request(
            "PUT",
            "/api/books/{$bookId}",
            $body,
            [],
            self::$header
        );

        $changedContent = json_decode(json_decode($client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertNotEmpty($changedContent);
        $this->assertArrayHasKey("id", $changedContent['data']);
        $this->assertArrayHasKey("title", $changedContent['data']);
        $this->assertSame("Changed title", $changedContent['data']['title']);
        $this->assertArrayHasKey("description", $changedContent['data']);
        $this->assertSame("Changed description", $changedContent['data']['description']);
        $this->assertArrayHasKey("type", $changedContent['data']);
        $this->assertSame("prose", $changedContent['data']['type']);

        $body = [];
        $body["title"] = "New title";
        $body["description"] = "New description";
        $body["type"] = "poetry";

        $client->request(
            "PUT",
            "/api/books/{$bookId}",
            $body,
            [],
            self::$header
        );

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testDestroy()
    {
        $client = static::createClient([]);

        /** @var AuthorRepository $authorRepository */
        $authorRepository = $client->getContainer()->get('doctrine')->getRepository(Author::class);
        $authors = $authorRepository->findOne();
        $authorId = $authors[0]->getId();
        $this->assertIsArray($authors);
        $this->assertNotEmpty($authorId);
        $this->assertIsInt($authorId);

        $body = [];
        $body["title"] = "Title for delete";
        $body["description"] = "Description for delete";
        $body["type"] = "poetry";
        $body["authors"] = [$authorId];

        $client->request(
            "POST",
            "/api/books/create",
            $body,
            [],
            self::$header
        );

        $content = json_decode(json_decode($client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $client->request(
            "DELETE",
            "/api/books/{$content['data']['id']}",
            [],
            [],
            self::$header
        );

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    private static function setToken(): void
    {
        $client = static::createClient([]);

        $client->request(
            "POST",
            "/api/auth/login",
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            json_encode(["username" => "admin@admin.admin", "password" => "123456"])
        );

        $content = json_decode($client->getResponse()->getContent());

        self::$token = $content->token;
        self::$header = [
            'HTTP_Authorization' => sprintf('%s %s', 'Bearer',  self::$token),
            'HTTP_CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT'       => 'application/json',
        ];
    }
}