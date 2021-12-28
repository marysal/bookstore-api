<?php

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\BookRepository;
use App\Entity\Book;
use App\Enum\StatusesOrdersEnum;

class XXX extends BaseTest
{

    public function testDestroy()
    {
        $client = static::createClient([]);
        $phone = "+375(33)257-12-12";
        $address = "Minsk, Leonardo Da Vinche str.";

        /** @var BookRepository $bookRepository */
        $bookRepository = $client->getContainer()->get('doctrine')->getRepository(Book::class);
        $books = $bookRepository->findOne();
        $booksId = $books[0]->getId();
        $this->assertIsArray($books);
        $this->assertNotEmpty($booksId);
        $this->assertIsInt($booksId);

        $bookList = [$booksId];

        $client->request('POST', '/api/orders/create',
            [
                'phone' => $phone,
                'address' => $address,
                'books' => $bookList
            ]
        );

        $content = json_decode(json_decode($client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $client->request(
            "DELETE",
            "/api/orders/{$content['data']['id']}",
            [],
            [],
            self::$header
        );

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
}