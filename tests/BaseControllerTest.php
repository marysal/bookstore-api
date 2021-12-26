<?php
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseControllerTest extends WebTestCase
{
    protected static $token;

    protected static $header;

    public static function setUpBeforeClass(): void
    {
        static::setToken();
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
            'HTTP_ACCEPT'       => 'application/json'
        ];
    }
}