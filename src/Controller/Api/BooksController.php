<?php

namespace App\Controller\Api;

use App\Entity\Author;
use App\Entity\Book;
use App\Traits\EntityManagerTrait;
use App\Traits\JsonPathTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class BooksController extends BaseController
{
    protected $relationEntity = Author::class;
    protected $entityName = "book";

    use EntityManagerTrait;
    use JsonPathTrait;

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
     * @Route("/api/books/create", name="app_api_books_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $book = $this->serializer->deserialize(
            $request->getContent(),
            Book::class,
            $request->getContentType()
        );

        $authors = $this->getIdsForLinkedTable($request);

        $this->setEntityRelations($book, $authors);

        $this->validate($book);

        $this->saveToDb($book);

        return $this->response(
            $book,
            $request->getAcceptableContentTypes(),
            Response::HTTP_CREATED
        );
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
     * @Route("/api/books/{id}", name="app_api_book_path_update", methods={"PATCH"})
     */
    public function partialUpdate(Request $request, Book $book): Response
    {
        $updatedData = $this->applyJsonPath($book, $request);

        $updatedBook = $this->serializer->deserialize(
            json_encode($updatedData),
            Book::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $book
            ],
        );

        $this->validate($updatedBook);

        $this->saveToDb($updatedBook);

        return $this->json(
            $this->getJsonContent($updatedBook)
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

        $this->setEntityRelations($book, $authors);

        $this->validate($book);

        $this->saveToDb($book);

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
        $this->saveToDb($book, "remove");

        return $this->response(
            [],
            $request->getAcceptableContentTypes()
        );
    }
}
