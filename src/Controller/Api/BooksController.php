<?php

namespace App\Controller\Api;

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\BookRepository;
use App\Service\ConvertorService;
use App\Service\EditEntityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
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
     * @var Serializer
     */
    private $serializer;

    /**
     * @var BookRepository
     */
    private $bookRepository;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param BookRepository $bookRepository
     * @param EntityManagerInterface $manager
     * @param EditEntityService $editEntityService
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        BookRepository $bookRepository,
        EntityManagerInterface $manager,
        EditEntityService $editEntityService,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $manager;
        $this->editEntityService = $editEntityService;
        $this->serializer = $serializer;
        $this->bookRepository = $bookRepository;
        $this->validator = $validator;
    }


    /**
     * @Route("/api/books", name="app_api_books_list", methods={"GET"})
     */
    public function list(Request $request)
    {
        $params = $request->query->all();

        $books = $this->bookRepository->findByFields($params);

        $jsonContent = $this->serializer->serialize(
            [
                'error' => false,
                'data' => $books
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
        #try {
            $title = $request->get('title', "");
            $description = $request->get('description', "");
            $type = $request->get('type', "");
            $authors = $request->get('authors', "");


            $book = new Book();
            $book->setTitle($title);
            $book->setDescription($description);
            $book->setType($type);


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

            //return $this->json($book);

            $jsonContent = $this->serializer->serialize(
                [
                    'status' => Response::HTTP_CREATED,
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

        #} catch (\Exception $e) {
            /*$jsonContent = $this->serializer->serialize(
                [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'errors' => $e->getMessage()
                ],
                'json'
            );*/
        #} finally {

        #}
        return $this->json($jsonContent);
    }

    /**
     * @Route("/api/books/{id}", name="app_api_book_show", methods={"GET"})
     */
    public function show(int $id): Response
    {
        try {
            $book = $this->bookRepository->find($id);

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
    public function update(Request $request, int $id): Response
    {
        try {
            $book = $this->bookRepository->find($id);
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

                $errors = (string) $this->validator->validate($book);

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
    public function destroy(int $id): Response
    {
        $book = $this->bookRepository->find($id);
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
