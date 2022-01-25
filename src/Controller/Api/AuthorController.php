<?php

namespace App\Controller\Api;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use App\Repository\OrderRepository;
use App\Service\EntityNormalizer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthorController extends BaseController
{
    protected static $entityName = "author";

    /**
     * @var AuthorRepository
     */
    protected $authorRepository;

    /**
     * @param AuthorRepository $authorRepository
     * @param OrderRepository $orderRepository
     * @param EntityNormalizer $entityNormalizer
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param EventDispatcherInterface $eventDispatcher
     * @param RequestStack $requestStack
     */
    public function __construct(
        AuthorRepository $authorRepository,
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

        $this->authorRepository = $authorRepository;
    }

    /**
     * @Route("/api/authors", name="app_api_authors_list", methods={"GET"})
     */
    public function list(Request $request)
    {
        $params = $request->query->all();

        $authors = $this->authorRepository->findByFields($params);

        return $this->response(
            $authors,
            $request->getAcceptableContentTypes()
        );
    }

    /**
     * @Route("/api/authors", name="app_api_authors_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $author = $this->serializer->deserialize(
            $request->getContent(),
            Author::class,
            $request->getContentType()
        );

        $this->validate($author);

        $this->entityNormalizer->saveToDb($author);

        return $this->response(
            $author,
            $request->getAcceptableContentTypes(),
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/api/authors/{id}", name="app_api_author_show", methods={"GET"})
     */
    public function show(Request $request, Author $author): Response
    {
        return $this->response(
            $author,
            $request->getAcceptableContentTypes()
        );
    }

    /**
     * @Route("/api/authors/{id}", name="app_api_author_update", methods={"PUT"})
     */
    public function update(Request $request, Author $author): Response
    {
        $author = $this->serializer->deserialize(
            $request->getContent(),
            Author::class,
            $request->getContentType(),
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $author
            ],
        );

        $this->validate($author);

        $this->entityNormalizer->saveToDb($author);

        return $this->response(
            $author,
            $request->getAcceptableContentTypes()
        );
    }

    /**
     * @Route("/api/authors/{id}", name="app_api_author_destroy", methods={"DELETE"})
     */
    public function destroy(Request $request, Author $author): Response
    {
        $this->entityNormalizer->removeFromDb($author);

        return $this->response(
            [],
            $request->getAcceptableContentTypes()
        );
    }
}