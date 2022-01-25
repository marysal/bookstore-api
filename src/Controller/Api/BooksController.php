<?php

namespace App\Controller\Api;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Service\ElasticSearchService;
use App\Service\EntityNormalizer;
use App\Service\JsonService;
use App\Service\PaginatorService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BooksController extends BaseController
{
    protected static $entityName = "books";

    /**
     * @var ElasticSearchService
     */
    protected $elasticSearch;

    /**
     * @var BookRepository
     */
    protected $bookRepository;

    /**
     * @var PaginatorService
     */
    protected $paginator;

    /**
     * @param BookRepository $bookRepository
     * @param EntityNormalizer $entityNormalizer
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param EventDispatcherInterface $eventDispatcher
     * @param RequestStack $requestStack
     * @param ElasticSearchService $elasticSearch
     */
    public function __construct(
        BookRepository $bookRepository,
        EntityNormalizer $entityNormalizer,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        PaginatorService $paginator,
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack,
        ElasticSearchService $elasticSearch
    ) {
        parent::__construct(
            $entityNormalizer,
            $serializer,
            $validator,
            $eventDispatcher,
            $requestStack
        );

        $this->bookRepository = $bookRepository;
        $this->paginator = $paginator;
        $this->elasticSearch = $elasticSearch;
    }

    /**
     * @Route("/api/books", name="app_api_books_list", methods={"GET"})
     */
    public function list(Request $request)
    {
        $params = $request->query->all();
        $page = $params["page"] ?? 1;

        $booksQuery = $this->bookRepository->getQueryByFields($params);


        return $this->response(
            $this->paginator->getPaginate($booksQuery, $page),
            $request->getAcceptableContentTypes()
        );
    }

    /**
     * @Route("/api/books", name="app_api_books_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {

        $book = $this->serializer->deserialize(
            $request->getContent(),
            Book::class,
            $request->getContentType()
        );

        $authors = $this->getIdsForLinkedTable($request);

        $this->entityNormalizer->setEntityRelations(
            self::$entityName,
            $book,
            $authors
        );

        $this->validate($book);

        $this->entityNormalizer->saveToDb($book);

        $response = $this->response(
            $book,
            $request->getAcceptableContentTypes(),
            Response::HTTP_CREATED
        );

        $this->elasticSearch->saveBookToElastic($book);

        return $response;
    }

    /**
     *
     * @Route("/api/books/{id}", name="app_api_book_show", methods={"GET"})
     */
    public function show(Request $request, Book $book): Response
    {
        return $this->response(
            $book,
            $request->getAcceptableContentTypes()
        );
    }

    /**
     * @Route("/api/books/{id}", name="app_api_book_patch_update", methods={"PATCH"})
     */
    public function partialUpdate(
        Request $request,
        Book $book,
        JsonService $jsonService
    ): Response {
        $updatedData = $jsonService->applyJsonPatch($book, $request, self::$entityName);

        $updatedBook = $this->serializer->deserialize(
            json_encode($updatedData),
            Book::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $book
            ],
        );

        $this->validate($updatedBook);

        $this->entityNormalizer->saveToDb($updatedBook);

        $this->elasticSearch->updateBookInElastic($request, $book);

        return $this->response(
            $updatedBook,
            $request->getAcceptableContentTypes()
        );
    }

    /**
     * @Route("/api/books/{id}", name="app_api_book_update", methods={"PUT"})
     */
    public function update(Request $request, Book $book): Response
    {

        $book = $this->serializer->deserialize(
            $request->getContent(),
            Book::class,
            $request->getContentType(),
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $book
            ],
        );

        $authors = $this->getIdsForLinkedTable($request);

        $this->entityNormalizer->setEntityRelations(
            self::$entityName,
            $book,
            $authors
        );

        $this->validate($book);

        $this->entityNormalizer->saveToDb($book);

        $this->elasticSearch->updateBookInElastic($request, $book);

        return $this->response(
            $book,
            $request->getAcceptableContentTypes()
        );
    }

    /**
     * @Route("/api/books/{id}", name="app_api_book_destroy", methods={"DELETE"})
     */
    public function destroy(Request $request, Book $book): Response
    {
        $this->entityNormalizer->removeFromDb($book);

        return $this->response(
            [],
            $request->getAcceptableContentTypes()
        );
    }


}
