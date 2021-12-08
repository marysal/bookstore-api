<?php

namespace App\Controller\Api;

use App\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BooksController extends AbstractController
{
    /**
     * @Route("/api/books", name="app_api_books_list", methods={"GET"})
     */
    public function list(): Response
    {
        return $this->json([
            'message' => 'Здесь будет получение книг'
        ]);
    }

    /**
     * @Route("/api/books/create", name="app_api_books_create", methods={"POST"})
     */
    public function create(): Response
    {
        $book = new Book();
        $book->setTitle('My first Book');
        $book->setDescription('My first Book My first Book');
        $book->setType('prose');


        return $this->json([
            'message' => 'Здесь будет создание книг'
        ]);
    }

    /**
     * @Route("/api/books/{id}", name="app_api_book_show", methods={"GET"})
     */
    public function show(): Response
    {
        return $this->json([
            'message' => 'Здесь будет поиск книги'
        ]);
    }

    /**
     * @Route("/api/books/{id}", name="app_api_book_update", methods={"PUT"})
     */
    public function update(): Response
    {
        return $this->json([
            'message' => 'Здесь будет обновление книги'
        ]);
    }

    /**
     * @Route("/api/books/{id}", name="app_api_book_destroy", methods={"DELETE"})
     */
    public function destroy(): Response
    {
        return $this->json([
            'message' => 'Здесь будет удаление книги'
        ]);
    }
}
