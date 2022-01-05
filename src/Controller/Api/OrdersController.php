<?php

namespace App\Controller\Api;

use App\Entity\Book;
use App\Entity\Order;
use App\Event\BeforeUpdateOrderEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class OrdersController extends BaseController
{
    protected $relationEntity = Book::class;
    protected $entityName = "order";

    /**
     * @Route("/api/orders", name="app_api_orders_list", methods={"GET"})
     */
    public function list(Request $request)
    {
        $params = $request->query->all();

        $orders = $this->orderRepository->findByFields($params);

        return $this->response(
            $orders,
            $request->getAcceptableContentTypes()
        );
    }

    /**
     * @Route("/api/orders/create", name="app_api_orders_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $order = $this->serializer->deserialize($request->getContent(), Order::class, 'json');

        $books = $this->getIdsForLinkedTable($request);

        $this->setEntityRelations($order, $books);

        $this->validate($order);

        $this->saveToDb($order);

        return $this->response(
            $order,
            $request->getAcceptableContentTypes(),
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_show", methods={"GET"})
     */
    public function show(Request $request, Order $order): Response
    {
        return $this->response(
            $order,
            $request->getAcceptableContentTypes()
        );
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_path_update", methods={"PATCH"})
     */
    public function partialUpdate(Request $request, Order $order): Response
    {
        $updatedData = $this->applyJsonPath($order, $request);

        $updatedOrder = $this->serializer->deserialize(
            json_encode($updatedData),
            Book::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $order
            ],
        );

        $this->validate($updatedOrder);

        $this->saveToDb($updatedOrder);

        return $this->response(
            $updatedOrder,
            $request->getAcceptableContentTypes()
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
            $request->getContentType(),
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $order
            ],
        );

        $books = $this->getIdsForLinkedTable($request);

        $this->setEntityRelations($order, $books);

        $this->validate($order);

        $this->saveToDb($order);

        return $this->response(
            $order,
            $request->getAcceptableContentTypes()
        );
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_destroy", methods={"DELETE"})
     */
    public function destroy(Request $request, Order $order): Response
    {
        $this->saveToDb($order, "remove");

        return $this->response(
            [],
            $request->getAcceptableContentTypes()
        );
    }
}