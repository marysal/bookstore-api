<?php

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\BookRepository;
use App\Entity\Book;
use App\Enum\StatusesOrdersEnum;

class OrdersController extends BaseControllerTest
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

    public function testShow()
    {
        $client = static::createClient([]);

        /** @var OrderRepository $orderRepository */
        $orderRepository = $client->getContainer()->get('doctrine')->getRepository(Order::class);
        $orders = $orderRepository->findOne();
        $orderId = $orders[0]->getId();

        $client->request(
            "GET",
            "/api/orders/{$orderId}"
        );

        $content = json_decode(json_decode($client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertSame($orderId, $content['data']['id']);
        $this->assertArrayHasKey("phone", $content['data']);
        $this->assertArrayHasKey("address", $content['data']);
        $this->assertArrayHasKey("status", $content['data']);
    }

    public function testUpdate()
    {
        $client = static::createClient([]);
        $newPhone = "375292571234";
        $newAddress = "Slavgorod";

        /** @var OrderRepository $orderRepository */
        $orderRepository = $client->getContainer()->get('doctrine')->getRepository(Order::class);
        $orders = $orderRepository->findOne();
        $orderId = $orders[0]->getId();

        $client->request(
            "GET",
            "/api/orders/{$orderId}"
        );

        $content = json_decode(json_decode($client->getResponse()->getContent()), true);

        $client->request(
            "PUT",
            "/api/orders/{$content['data']['id']}",
            [
                'phone' => $newPhone,
                'address' => $newAddress,
                'status' => StatusesOrdersEnum::STATUS_DELIVERED,
                'books' => [$content['data']['bookOrderList'][0]['id']]
            ],
            [],
            self::$header
        );

        $changedContent = json_decode(json_decode($client->getResponse()->getContent()), true);

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertArrayHasKey("phone", $content['data']);
        $this->assertSame($content['data']['id'], $changedContent['data']['id']);
        $this->assertNotEquals($content['data']['phone'], $changedContent['data']['phone']);
        $this->assertNotEquals($content['data']['address'], $changedContent['data']['address']);
        $this->assertSame($newAddress, $changedContent['data']['address']);
        $this->assertSame($newPhone, $changedContent['data']['phone']);
    }

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