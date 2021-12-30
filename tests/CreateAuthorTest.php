<?php

use Symfony\Component\HttpFoundation\Response;

class CreateAuthorTest extends BaseTest
{
    /**
     * @dataProvider authorDataProvider
     */
    public function testCreate($message, $name)
    {
        self::$client->request('POST', '/api/authors/create',
            self::$singleAuthor
        );

        $content = json_decode(self::$client->getResponse()->getContent());

        $this->assertSame(Response::HTTP_UNAUTHORIZED, self::$client->getResponse()->getStatusCode());
        $this->assertSame($message, $content->message);

        self::$client->request(
            "POST",
            "/api/authors/create",
            self::$singleAuthor,
            [],
            self::$header,
            json_encode(self::$singleAuthor)
        );

        $content = json_decode(json_decode(self::$client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_CREATED, self::$client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey("id", $content['data']);
        $this->assertArrayHasKey("name", $content['data']);
        $this->assertSame($name, $content['data']['name']);

        $this->lastAuthorId = $content['data']['id'];
    }

    public function authorDataProvider()
    {
        return [
            [
                "message" => "JWT Token not found",
                "name" => "Fedor Dostojevskij"
            ]
        ];
    }

    protected function tearDown(): void
    {
        $query = $this->em->createQuery(
            'DELETE FROM App\Entity\Author a
             WHERE a.id = :id'
        )
        ->setParameter('id', $this->getLastAuthorId());

        $query->execute();
    }

}