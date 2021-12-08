<?php

namespace App\Controller\Api;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Service\ConvertorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BooksController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->entityManager = $manager;
    }

    /**
     * @Route("/api/books", name="app_api_books_list", methods={"GET"})
     */
    public function list(Request $request, BookRepository $bookRepository)
    {
        $params = $request->query->all();

        if (empty($params)) {
            $books = $bookRepository->findAll();
        } else {
            $books = $bookRepository->findByFields($params);
        }

        return $this->json(
            [
                'error' => false,
                'data' => ConvertorService::convertBookObjectToArray($books)
            ]
        );
    }

    /**
     * @Route("/api/books/create", name="app_api_books_create", methods={"POST"})
     */
    public function create(Request $request, ValidatorInterface $validator): Response
    {
        try{
            $title = $request->get('title', "");
            $description = $request->get('description', "");
            $type = $request->get('type', "");

            $book = new Book();
            $book->setTitle($title);
            $book->setDescription($description);
            $book->setType($type);

            $errors = (string) $validator->validate($book);

             if(!empty($errors)) {
                throw new \Exception($errors);
             }

            $this->entityManager->persist($book);
            $this->entityManager->flush();

            $data = [
                'status' => 200,
                'success' => "Book added successfully",
            ];

        } catch (\Exception $e) {
            $errors = $e->getMessage() ?? "Data no valid";
            $data = [
                'status' => 422,
                'errors' => $errors
            ];
        } finally {
            return $this->json($data);
        }
    }

    /**
     * @Route("/api/books/{id}", name="app_api_book_show", methods={"GET"})
     */
    public function show(int $id, BookRepository $bookRepository): Response
    {
        $book = $bookRepository->find($id);

        return $this->json(
            [
                'error' => false,
                'data' => ConvertorService::convertBookObjectToArray($book)
            ]
        );
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
