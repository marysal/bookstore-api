<?php

namespace App\Controller\Api;

use App\Entity\Book;
use App\Service\JsonService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class BooksController extends BaseController
{
    protected static $entityName = "books";

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

        $this->saveToElastic($response);

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
        $updatedData = $jsonService->applyJsonPatch($book, $request, $this->entityName);

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

    /**
     * @param Response $response
     * @return bool
     */
    private function saveToElastic(Response $response): bool
    {
        $book = json_decode($response->getContent(), true);

        $params = [
            'index' => 'app',
            'type'    => 'book',
            'body'  => [
                'id' => $book["data"]["id"],
                'title' => $book["data"]["title"],
                'description' => $book["data"]["description"],
                'type' => $book["data"]["type"],
                'authors' => []
            ]
        ];

        foreach ($book["data"]["authors"] as $key => $author) {
            $params["body"]["authors"][$key] = [
                'id' => $author["id"],
                'name' => $author["name"]
            ];
        }

        $this->client->index($params);

        return true;
    }
}
