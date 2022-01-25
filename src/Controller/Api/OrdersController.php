<?php

namespace App\Controller\Api;

use App\Entity\Order;
use App\Enum\ActionsGroupEnum;
use App\Event\BeforeUpdateOrderEvent;
use App\Repository\OrderRepository;
use App\Service\EntityNormalizer;
use App\Service\JsonService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrdersController extends BaseController
{
    protected static $entityName = "orders";

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @param OrderRepository $orderRepository
     * @param EntityNormalizer $entityNormalizer
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param EventDispatcherInterface $eventDispatcher
     * @param RequestStack $requestStack
     */
    public function __construct(
        OrderRepository $orderRepository,
        EntityNormalizer $entityNormalizer,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack
    ) {
        parent::__construct(
            $entityNormalizer,
            $serializer,
            $validator,
            $eventDispatcher,
            $requestStack
        );

        $this->orderRepository = $orderRepository;
    }

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
     * @Route("/api/orders", name="app_api_orders_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $event = new BeforeUpdateOrderEvent($this->getUser(), $request);

        $order = $this->serializer->deserialize(
            $request->getContent(),
            Order::class,
            $request->getContentType()
        );

        $books = $this->getIdsForLinkedTable($request, "books");

        $this->entityNormalizer->setEntityRelations(
            self::$entityName,
            $order,
            $books
        );

        $this->validate($order);

        $this->entityNormalizer->saveToDb($order);

        $this->eventDispatcher->dispatch($event, 'order.after_validate');

        return $this->response(
            $order,
            $request->getAcceptableContentTypes(),
            Response::HTTP_CREATED,
            self::$entityName
        );
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_show", methods={"GET"})
     */
    public function show(Request $request, Order $order): Response
    {
        return $this->response(
            $order,
            $request->getAcceptableContentTypes(),
            Response::HTTP_OK,
            self::$entityName
        );
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_patch_update", methods={"PATCH"})
     */
    public function partialUpdate(
        Request $request,
        Order $order,
        JsonService $jsonService
    ): Response {
        $updatedData = $jsonService->applyJsonPatch($order, $request, self::$entityName);

        $updatedOrder = $this->serializer->deserialize(
            json_encode($updatedData),
            Order::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $order
            ],
        );

        $this->validate($updatedOrder);

        $this->entityNormalizer->saveToDb($updatedOrder);

        return $this->response(
            $updatedOrder,
            $request->getAcceptableContentTypes(),
            Response::HTTP_OK,
            self::$entityName
        );
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_update", methods={"PUT"})
     */
    public function update(Request $request, Order $order): Response
    {
        $event = new BeforeUpdateOrderEvent(
            $this->getUser(),
            $request,
            ActionsGroupEnum::UPDATE
        );

        $this->eventDispatcher->dispatch($event, 'order.pre_update');

        $order = $this->serializer->deserialize(
            $request->getContent(),
            Order::class,
            $request->getContentType(),
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $order
            ],
        );

        $books = $this->getIdsForLinkedTable($request, "books");

        $this->entityNormalizer->setEntityRelations(
            self::$entityName,
            $order,
            $books
        );

        $this->validate($order);

        $this->eventDispatcher->dispatch($event, 'order.after_validate');

        $this->entityNormalizer->saveToDb($order);

        return $this->response(
            $order,
            $request->getAcceptableContentTypes(),
            Response::HTTP_OK,
            self::$entityName
        );
    }

    /**
     * @Route("/api/orders/{id}", name="app_api_order_destroy", methods={"DELETE"})
     */
    public function destroy(Request $request, Order $order): Response
    {
        $this->entityNormalizer->removeFromDb($order);

        return $this->response(
            [],
            $request->getAcceptableContentTypes()
        );
    }
}