<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthorControllerTest extends WebTestCase
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
             "/api/authors"
         );

         $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testAccessCreate()
    {
        $client = static::createClient([]);

        $client->request('POST', '/api/authors/create',
            [
                'name' => "Fedor Dostojevskij",
            ]
        );

        $content = json_decode($client->getResponse()->getContent());

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
        $this->assertSame("JWT Token not found", $content->message);
    }

    public function testCreate()
    {
        $client = static::createClient([]);

        $client->request(
    "POST",
        "/api/authors/create",
            ['name' => "Fyodor Dostoevsky"],
            [],
            self::$header
        );

        $content = json_decode(json_decode($client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey("name", $content['data']);
        $this->assertSame("Fyodor Dostoevsky", $content['data']['name']);
    }

    public function testShow()
    {
        $client = static::createClient([]);

        $client->request(
            "POST",
            "/api/authors/create",
            ['name' => "Fyodor Dostoevsky"],
            [],
            self::$header
        );

        $content = json_decode(json_decode($client->getResponse()->getContent()), true);
        $authorId = $content['data']['id'];

        $client->request(
            "GET",
            "/api/authors/{$authorId}"
        );
        $author = json_decode(json_decode($client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey("name", $content['data']);
        $this->assertSame("Fyodor Dostoevsky", $content['data']['name']);
        $this->assertSame($authorId, $author['data']['id']);
    }

    public function testUpdate()
    {
        $client = static::createClient([]);

        $client->request(
            "POST",
            "/api/authors/create",
            ['name' => "Fyodor Dostoevsky"],
            [],
            self::$header
        );

        $content = json_decode(json_decode($client->getResponse()->getContent()), true);

        $client->request(
            "PUT",
            "/api/authors/{$content['data']['id']}",
            ['name' => "Fyodor Dostoevsky New"],
            [],
            self::$header
        );

        $changedContent = json_decode(json_decode($client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey("name", $content['data']);
        $this->assertSame($content['data']['id'], $changedContent['data']['id']);
        $this->assertNotEquals($content['data']['name'], $changedContent['data']['name']);
        $this->assertSame("Fyodor Dostoevsky New", $changedContent['data']['name']);
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