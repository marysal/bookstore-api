<?php

namespace App\Controller\Api;

use App\Entity\Author;
use App\Entity\Book;
use App\Enum\EntityGroupsEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class BooksController extends BaseController
{
    /**
     * @Route("/api/books", name="app_api_books_list", methods={"GET"})
     */
    public function list(Request $request)
    {
        $params = $request->query->all();

        $booksQuery = $this->bookRepository->getQueryByFields($params);

        return $this->json(
            $this->getJsonContent(
                $this->paginator->getPaginate($booksQuery)
            ),
        );
    }

    /**
     * @Route("/api/books/create", name="app_api_books_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $book = $this->serializer->deserialize($request->getContent(), Book::class, 'json');
        $authors = $request->get('authors', []);

        foreach ($authors as $authorId) {
            $authorId = (int)$authorId;
            $author = $this->entityManager->find(Author::class, $authorId);
            $this->validate($author);
            $book->appendAuthor($author);
        }

        $this->validate($book);

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $this->json(
            $this->getJsonContent($book),
            Response::HTTP_CREATED
        );
    }

    /**
     *
     * @Route("/api/books/{id}", name="app_api_book_show", methods={"GET"})
     */
    public function show(Book $book): Response
    {
        return $this->json(
            $this->getJsonContent($book)
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
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $book
            ],
        );

        $authors = $request->get('authors', []);

        foreach ($authors as $authorId) {
            $authorId = (int)$authorId;
            $author = $this->entityManager->find(Author::class, $authorId);
            $this->validate($author);
            $book->appendAuthor($author);
        }

        $this->validate($book);

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $this->json(
            $this->getJsonContent($book)
        );
    }

    /**
     * @Route("/api/books/{id}", name="app_api_book_destroy", methods={"DELETE"})
     */
    public function destroy(Book $book): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($book);
        $manager->flush();

        return $this->json(
            $this->getJsonContent([], EntityGroupsEnum::ENTITY_DELETED)
        );
    }
}
