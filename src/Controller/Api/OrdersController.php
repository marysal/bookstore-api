<?php

namespace App\Controller\Api;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrdersController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param EntityManagerInterface $manager
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManagerInterface $manager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        OrderRepository $orderRepository
    ) {
        $this->entityManager = $manager;
        $this->serializer = $serializer;
        $this->orderRepository = $orderRepository;
        $this->validator = $validator;
    }

    /**
     * @Route("/api/orders", name="app_api_orders_list", methods={"GET"})
     */
    public function list(Request $request)
    {
        $params = $request->query->all();

        $orders = $this->orderRepository->findByFields($params);

        $jsonContent = $this->serializer->serialize(
            [
                'data' => $orders
            ],
            'json',
            [
                'groups' => [
                    'order',
                    'book',
                    'book_order'
                ]
            ]
        );

        return $this->json($jsonContent);
    }

    /**
     * @Route("/api/orders/create", name="app_api_orders_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $phone = $request->get('phone', "");
        $address = $request->get('address', "");
        $books = $request->get('books', []);

        $order = new Order();
        $order->setPhone($phone);
        $order->setAddress($address);

        if(empty($books)) {
            throw $this->createNotFoundException('The order must contain at least one book ID');
        }

        foreach ($books as $bookId) {
            $bookId = (int) $bookId;
            $book = $this->entityManager->find(Book::class, $bookId);
            if (empty($book)) {
                throw $this->createNotFoundException('Book with this ID not found');
            }
            $order->appendBookOrderList($book);
        }

        $errors = (string) $this->validator->validate($order);

        if(!empty($errors)) {
            throw $this->createNotFoundException($errors);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $jsonContent = $this->serializer->serialize(
            [
                'data' => $order
            ],
            'json',
            [
                'groups' => [
                    'order',
                    'book',
                    'book_order'
                ]
            ]
        );

        return $this->json($jsonContent, Response::HTTP_CREATED);
    }
}