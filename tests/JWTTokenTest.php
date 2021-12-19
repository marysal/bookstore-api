<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class JWTTokenTest extends WebTestCase
{
    public function testJWTLogin()
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
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content->token);
    }
}