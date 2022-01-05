<?php

namespace App\Controller\Api;

use App\Enum\EntityGroupsEnum;
use App\Enum\ResponseContentTypesEnum;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\OrderRepository;
use App\Service\PaginatorService;
use App\Traits\EntityManagerTrait;
use App\Traits\JsonPathTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class BaseController extends AbstractController
{
    use EntityManagerTrait;
    use JsonPathTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var Serializer|SerializerInterface
     */
    protected $serializer;

    /**
     * @var BookRepository
     */
    protected $bookRepository;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var AuthorRepository
     */
    protected $authorRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var PaginatorService
     */
    protected $paginator;
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param BookRepository $bookRepository
     * @param AuthorRepository $authorRepository
     * @param OrderRepository $orderRepository
     * @param EntityManagerInterface $manager
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     * @param PaginatorService $paginator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        BookRepository         $bookRepository,
        AuthorRepository       $authorRepository,
        OrderRepository        $orderRepository,
        EntityManagerInterface $manager,
        SerializerInterface    $serializer,
        ValidatorInterface     $validator,
        PaginatorService       $paginator,
        EventDispatcherInterface $eventDispatcher
    )
    {

        $this->entityManager = $manager;
        $this->serializer = $serializer;
        $this->bookRepository = $bookRepository;
        $this->validator = $validator;
        $this->authorRepository = $authorRepository;
        $this->orderRepository = $orderRepository;
        $this->paginator = $paginator;
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * @param $data
     * @param string $typeJsonContent
     * @return string
     */
    protected function getJsonContent($data, string $typeJsonContent = "books"): string
    {
        $group = EntityGroupsEnum::getEntityGroupsList();

        return $this->serializer->serialize(
            [
                'data' => $data
            ],
            'json',
            [
                'groups' => $group[$typeJsonContent]
            ]
        );
    }

    /**
     * @param $data
     * @param string $typeJsonContent
     * @return string
     */
    private function getSerializedContent($data, string $format = "json", string $typeJsonContent = "books"): string
    {
        $group = EntityGroupsEnum::getEntityGroupsList();

        return $this->serializer->serialize(
            [
                'data' => $data
            ],
            $format,
            [
                'groups' => $group[$typeJsonContent]
            ]
        );
    }

    /**
     * @param $entity
     * @throws NotFoundHttpException
     * @return bool|null
     */
    protected function validate($entity, $typeValidate = "existanceEntity"): void
    {
        if (empty($entity) && $typeValidate == "existanceEntity") {
            throw $this->createNotFoundException('Object with this ID not found');
        }

        $errors = $this->validator->validate($entity);

        if ($errors->has(0)) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                $errors->get(0)->getPropertyPath(). ": ".$errors->get(0)->getMessage()
            );
        }
    }

    /**
     * @param object|array $data
     * @param array|string[] $acceptableContentTypes
     * @param string|int $responseStatus
     * @return Response
     */
    protected function response(
        $data = [],
        array $acceptableContentTypes = ["json"],
        string $responseStatus = Response::HTTP_OK
    ): Response {

        $contentType = $this->getContentType($acceptableContentTypes);

        $response = new Response();

        $response->setContent($this->getSerializedContent(
            $data,
            $contentType
        ));

        $response->setStatusCode($responseStatus);
        $response->headers->set('Content-Type', "application/{$contentType}");

        return $response;
    }

    /**
     * @param $acceptableContentTypes
     * @return string
     */
    public function getContentType($acceptableContentTypes): string
    {
        $contentType = "json";

        foreach ($acceptableContentTypes as $contentType) {
            if(!in_array($contentType, ResponseContentTypesEnum::getContentTypesList())) {
                continue;
            }

            return str_replace("application/", "", $contentType);
        }

        return $contentType;
    }

    /**
     * @param Request $request
     * @param string $tableName
     * @return string[]
     */
    protected function getIdsForLinkedTable(
        Request $request,
        string $tableName = "authors"
    ): array {
        $ids = $request->get($tableName, []);

        if($request->getContentType() == "xml") {
            $xml = simplexml_load_string($request->getContent(), "SimpleXMLElement", LIBXML_NOCDATA);
            $json = json_encode($xml);
            $authors = json_decode($json,true);
            $ids = $authors[$tableName]["id"];
            if(is_string($ids)) {
                $ids = [$ids];
            }
        }

        return $ids;
    }
}