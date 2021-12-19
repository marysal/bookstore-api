<?php

use App\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\BookRepository;
use App\Entity\Book;
use App\Enum\StatusesOrdersEnum;

class OrdersControllerTest extends WebTestCase
{
    public function testList()
    {
        $client = static::createClient([]);

        $client->request(
            "GET",
            "/api/orders"
        );

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testCreate()
    {
        $client = static::createClient([]);
        $phone = "+375(29)257-12-33";
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

        $content = json_decode(json_decode($client->getResponse()->getContent(), true), true);

        $this->assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $order = $client->getContainer()->get('doctrine')->getRepository(Order::class)->findOneBy([
            'id' => $content['data']['id']
        ]);

        $this->assertNotEmpty($order);
        $this->assertSame($address, $order->getAddress());
        $this->assertSame($phone, $order->getPhone());
        $this->assertSame(StatusesOrdersEnum::STATUS_PENDING, $order->getStatus());
    }
}