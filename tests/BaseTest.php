<?php

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

    protected static $client;

    protected static $token;

    protected static $header;

    public static function setUpBeforeClass(): void
    {
        self::ensureKernelShutdown();
        self::$client = static::createClient([]);
        static::setToken();
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

    public function authorDataProvider()
    {
        return [
            [
                "name" => "Fedor Dostojevskij"
            ]
        ];
    }
}