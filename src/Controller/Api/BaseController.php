<?php

namespace App\Controller\Api;

use App\Enum\EntityGroupsEnum;
use App\Enum\ResponseContentTypesEnum;
use App\Service\EntityNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class BaseController extends AbstractController
{
    /**
     * @var EntityNormalizer
     */
    protected $entityNormalizer;

    /**
     * @var Serializer|SerializerInterface
     */
    protected $serializer;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @param EntityNormalizer $entityNormalizer
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     * @param EventDispatcherInterface $eventDispatcher
     * @param RequestStack $requestStack
     */
    public function __construct(
        EntityNormalizer       $entityNormalizer,
        SerializerInterface    $serializer,
        ValidatorInterface     $validator,
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack
    ) {
        $this->entityNormalizer = $entityNormalizer;
        $this->serializer = $serializer;

        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestStack = $requestStack;
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
        string $responseStatus = Response::HTTP_OK,
        string $typesSerializeContent = "books"
    ): Response {

        $contentType = $this->getAcceptableContentType($acceptableContentTypes);

        $response = new Response();

        $response->setContent($this->getSerializedContent(
            $data,
            $this->requestStack->getCurrentRequest()->getFormat($acceptableContentTypes[0]),
            $typesSerializeContent
        ));

        $response->setStatusCode($responseStatus);
        $response->headers->set('Content-Type', $contentType);

        return $response;
    }

    /**
     * @param $acceptableContentTypes
     * @return string
     */
    public function getAcceptableContentType($acceptableContentTypes): string
    {
        $contentType = null;

        foreach ($acceptableContentTypes as $acceptableContentType) {
            if(!in_array($acceptableContentType, ResponseContentTypesEnum::getContentTypesList())) {
                continue;
            }

            $contentType = $acceptableContentType;
        }

        if (empty($contentType)) {
            throw new HttpException(
                Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE,
                "The request content type is not supported by the service."
            );
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
            $ids = [];
            $receivedAuthorsIds = $authors[$tableName]["id"] ?? [];

            if(is_string($receivedAuthorsIds)) {
                $ids = [$receivedAuthorsIds];
            } elseif (is_array($receivedAuthorsIds)) {
                foreach ($receivedAuthorsIds as $id) {
                    array_push($ids, $id);
                }
            }
        }

        return $ids;
    }
}