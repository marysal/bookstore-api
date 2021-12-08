<?php

namespace App\Controller\Api;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Service\ConvertorService;
use App\Service\EditEntityService;
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
     * @var EditEntityService
     */
    private $editEntityService;

    /**
     * @param EntityManagerInterface $manager
     * @param EditEntityService $editEntityService
     */
    public function __construct(EntityManagerInterface $manager, EditEntityService $editEntityService)
    {
        $this->entityManager = $manager;
        $this->editEntityService = $editEntityService;
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
        try {
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
                'status' => Response::HTTP_OK,
                'success' => "Book added successfully",
            ];

        } catch (\Exception $e) {
            $data = [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => $e->getMessage()
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
        try {
            $book = $bookRepository->find($id);

            if (empty($book)) {
                throw new \Exception("Book not found");
            }

            $data =  [
                'error' => false,
                'data' => ConvertorService::convertBookObjectToArray($book)
            ];
        } catch (\Exception $e) {
            $errors = $e->getMessage();
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => $errors
            ];
        } finally {
            return $this->json($data);
        }
    }

    /**
     * @Route("/api/books/{id}", name="app_api_book_update", methods={"PUT"})
     */
    public function update(
        Request $request,
        int $id,
        BookRepository $bookRepository,
        ValidatorInterface $validator
    ): Response {
        try {
            $book = $bookRepository->find($id);
            //$title = $request->get('title', "");
            //$description = $request->get('description', "");
            //$type = $request->get('type', "");
            //$authors = $request->get('authors', []);
            if ($book) {
                //$changedBook = $this->editEntityService->changeBook(
                    //$book,
                    //$request->get('title', ""),
                    //$request->get('title', ""),
                    //$request->get('type', "")
                //$authors
                //);

                if (!empty($request->get('title', ""))) {
                    $book->setTitle($request->get('title', ""));
                }

                if(!empty($request->get('title', ""))) {
                    $book->setDescription($request->get('title', ""));
                }

                if(!empty($request->get('type', ""))) {
                    $book->setType($request->get('type', ""));
                }

                $errors = (string) $validator->validate($book);

                $this->entityManager->persist($book);
                $this->entityManager->flush();

                if(!empty($errors)) {
                    throw new \Exception($errors);
                }

                $data = [
                    'status' => Response::HTTP_OK,
                ];

            } else {
                $data = [
                    'status' => Response::HTTP_NOT_FOUND,
                    'errors' => "Book not found"
                ];
            }
        } catch (\Exception $e) {
            $errors = $e->getMessage();
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => $errors
            ];
        } finally {
            return $this->json($data);
        }
    }

    /**
     * @Route("/api/books/{id}", name="app_api_book_destroy", methods={"DELETE"})
     */
    public function destroy(int $id, BookRepository $bookRepository): Response
    {
        $book = $bookRepository->find($id);
        if ($book) {
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($book);
            $manager->flush();
            $data = [
                "errors" => false,
                'message' => 'Deleted'
            ];
        } else {
            $data = [
                "status" => Response::HTTP_NOT_FOUND,
                'errors' => 'Book not found'
            ];
        }
        return $this->json($data);
    }
}
