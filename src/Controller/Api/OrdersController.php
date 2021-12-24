<?php

namespace App\Controller\Api;

use App\Entity\Book;
use App\Entity\Order;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrdersController extends BaseController
{
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

    /**
     * @Route("/api/orders/{id}", name="app_api_order_show", methods={"GET"})
     */
    public function show(Order $order): Response
    {
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

        return $this->json($jsonContent);
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_update", methods={"PUT"})
     */
    public function update(Request $request, Order $order): Response
    {
        $books = $request->get('books', []);

        $order->setPhone($request->get('phone', ""));
        $order->setAddress($request->get('address', ""));
        $order->setStatus($request->get('status', ""));

        if(empty($books)) {
            throw $this->createNotFoundException('The order must contain at least one book ID');
        }

        foreach ($books as $bookId) {
            $bookId = (int) $bookId;
            $book = $this->entityManager->find(Book::class, $bookId);
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

        return $this->json($jsonContent);
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_destroy", methods={"DELETE"})
     */
    public function destroy(Order $order): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($order);
        $manager->flush();
        $data = [
            "errors" => false,
            'message' => 'Deleted'
        ];

        $jsonContent = $this->serializer->serialize($data, 'json');

        return $this->json($jsonContent);
    }
}