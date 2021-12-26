<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthorController extends BaseControllerTest
{
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

    public function testDestroy()
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

        $client->request(
            "DELETE",
            "/api/authors/{$content['data']['id']}",
            [],
            [],
            self::$header
        );

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
}