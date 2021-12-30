<?php

use Symfony\Component\HttpFoundation\Response;

class JWTTokenTest extends BaseTest
{

    /**
     * @dataProvider jwtDataProvider
     */
    public function testJWTLogin($username, $password, $responseCode, $message)
    {
      self::$client->request(
  "POST",
      "/api/auth/login",
          [],
          [],
          ["CONTENT_TYPE" => "application/json"],
          json_encode(["username" => $username, "password" => $password])
      );

      $content = json_decode(self::$client->getResponse()->getContent(), true);

      $this->assertSame($responseCode, self::$client->getResponse()->getStatusCode());

      if ($responseCode != Response::HTTP_OK) {
          $this->assertArrayHasKey("message", $content);
          $this->assertSame($message, $content["message"]);
      } else {
          $this->assertNotEmpty($content["token"]);
      }

    }

    public function jwtDataProvider()
    {
        return [
            [
                "username" => "admin@admin.admin",
                "password" => "123456",
                "responseCode" => Response::HTTP_OK,
                "message" => ""
            ],
            [
                "username" => "admin",
                "password" => "123456",
                "responseCode" => Response::HTTP_UNAUTHORIZED,
                "message" => "Invalid credentials."
            ],
            [
                "username" => "admin",
                "password" => "",
                "responseCode" => Response::HTTP_UNAUTHORIZED,
                "message" => "Invalid credentials."
            ],
            [
                "username" => "admin@admin.admin",
                "password" => "",
                "responseCode" => Response::HTTP_UNAUTHORIZED,
                "message" => "Invalid credentials."
            ],
        ];
    }
}