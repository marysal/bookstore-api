<?php

namespace App\Tests;

class MainXmlTest extends BaseTest
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


    protected static function setToken(): void
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
            'HTTP_CONTENT_TYPE' => 'application/xml',
            'CONTENT_TYPE' => 'application/xml',
            'HTTP_ACCEPT'  => 'application/xml'
        ];
    }

    function object2array($object) {
        return @json_decode(@json_encode($object),1);
    }
}