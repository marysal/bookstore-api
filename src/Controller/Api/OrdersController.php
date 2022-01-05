<?php

namespace App\Controller\Api;

use App\Entity\Book;
use App\Entity\Order;
use App\Enum\EntityGroupsEnum;
use App\Event\BeforeUpdateOrderEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class OrdersController extends BaseController
{
    /**
     * @Route("/api/orders", name="app_api_orders_list", methods={"GET"})
     */
    public function list(Request $request)
    {
        $params = $request->query->all();

        $orders = $this->orderRepository->findByFields($params);

        return $this->json(
            $this->getJsonContent($orders, EntityGroupsEnum::ENTITY_ORDERS)
        );
    }

    /**
     * @Route("/api/orders/create", name="app_api_orders_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $order = $this->serializer->deserialize($request->getContent(), Order::class, 'json');
        $books = $request->get('books', []);

        if(empty($books)) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                "The order must contain at least one book ID"
            );
        }

        foreach ($books as $bookId) {
            $bookId = (int) $bookId;
            $book = $this->entityManager->find(Book::class, $bookId);
            $this->validate($book);
            $order->appendBookOrderList($book);
        }

        $this->validate($order);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $this->json(
            $this->getJsonContent($order, EntityGroupsEnum::ENTITY_ORDERS),
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_show", methods={"GET"})
     */
    public function show(Order $order): Response
    {
        return $this->json(
            $this->getJsonContent($order, "orders")
        );
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_update", methods={"PUT"})
     */
    public function update(Request $request, Order $order): Response
    {
        $event = new BeforeUpdateOrderEvent($this->getUser(), $request);
        $this->eventDispatcher->dispatch($event, 'order.pre_update');

        $order = $this->serializer->deserialize(
            $request->getContent(),
            Order::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $order
            ],
        );

        $books = $request->get('books', []);

        $this->validate($books);

       /* if(empty($books)) {
            throw $this->createNotFoundException('The order must contain at least one book ID');
        }*/

        foreach ($books as $bookId) {
            $bookId = (int) $bookId;
            $book = $this->entityManager->find(Book::class, $bookId);
            $this->validate($book);
            $order->appendBookOrderList($book);
        }

        $this->validate($order);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $this->json(
            $this->getJsonContent($order, EntityGroupsEnum::ENTITY_ORDERS)
        );
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_destroy", methods={"DELETE"})
     */
    public function destroy(Order $order): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($order);
        $manager->flush();

        return $this->json(
            $this->getJsonContent([], EntityGroupsEnum::ENTITY_DELETED)
        );
    }
}