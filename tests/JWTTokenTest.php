<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class JWTTokenTest extends BaseTest
{
    public function testJWTLogin()
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
        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content->token);
    }
}