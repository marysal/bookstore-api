<?php

namespace App\Controller\Api;

use App\Entity\Author;
use App\Entity\Book;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BooksController extends BaseController
{
    /**
     * @Route("/api/books", name="app_api_books_list", methods={"GET"})
     */
    public function list(Request $request)
    {
        $params = $request->query->all();

        $booksQuery = $this->bookRepository->getQueryByFields($params);

        $jsonContent = $this->serializer->serialize(
            [
                'data' => $this->paginator->getPaginate($booksQuery)
            ],
     'json',
            [
                'groups' => [
                    'book',
                    'author',
                    'author_detail' /* if you add "book_detail" here you get circular reference */
                ]
            ]
        );

        return $this->json($jsonContent);
    }

    /**
     * @Route("/api/books/create", name="app_api_books_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $book = $this->serializer->deserialize($request->getContent(), Book::class, 'json');
        $authors = $request->get('authors', []);

        foreach ($authors as $authorId) {
            $authorId = (int) $authorId;
            $author = $this->entityManager->find(Author::class, $authorId);
            if (empty($author)) {
                throw $this->createNotFoundException('Author with this ID not found');
            }
            $book->appendAuthor($author);
        }

        $errors = (string) $this->validator->validate($book);

        if(!empty($errors)) {
            throw $this->createNotFoundException($errors);
        }

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        $jsonContent = $this->serializer->serialize(
            [
                'data' => $book
            ],
     'json',
            [
                'groups' => [
                    'book',
                    'author',
                    'author_detail' /* if you add "book_detail" here you get circular reference */
                ]
            ]
        );

        return $this->json($jsonContent, Response::HTTP_CREATED);
    }

    /**
     *
     * @Route("/api/books/{id}", name="app_api_book_show", methods={"GET"})
     */
    public function show(Book $book): Response
    {
        //var_dump($book);die();

        if (!$book) {
            throw $this->createNotFoundException('The product does not exist');
        }

        $jsonContent = $this->serializer->serialize(
            [
                'data' => $book
            ],
            'json',
            [
                'groups' => [
                    'book',
                    'author',
                    'author_detail' /* if you add "book_detail" here you get circular reference */
                ]
            ]
        );

        return $this->json($jsonContent);
    }

    /**
     * @Route("/api/books/{id}", name="app_api_book_update", methods={"PUT"})
     */
    public function update(Request $request, Book $book): Response
    {
        $authors = $request->get('authors', []);

        $book->setTitle($request->get('title', ""));
        $book->setDescription($request->get('description', ""));
        $book->setType($request->get('type', ""));

        foreach ($authors as $authorId) {
            $authorId = (int) $authorId;
            $author = $this->entityManager->find(Author::class, $authorId);
            if (empty($author)) {
                throw $this->createNotFoundException('Author with this ID not found');
            }
            $book->appendAuthor($author);
        }

        $errors = (string) $this->validator->validate($book);

        if(!empty($errors)) {
            throw $this->createNotFoundException($errors);
        }

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        $jsonContent = $this->serializer->serialize(
            [
                'data' => $book
            ],
            'json',
            [
                'groups' => [
                    'book',
                    'author',
                    'author_detail' /* if you add "book_detail" here you get circular reference */
                ]
            ]
        );

        return $this->json($jsonContent);
    }

    /**
     * @Route("/api/books/{id}", name="app_api_book_destroy", methods={"DELETE"})
     */
    public function destroy(Book $book): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($book);
        $manager->flush();
        $data = [
            "errors" => false,
            'message' => 'Deleted'
        ];

        $jsonContent = $this->serializer->serialize($data, 'json');

        return $this->json($jsonContent);
    }
}
