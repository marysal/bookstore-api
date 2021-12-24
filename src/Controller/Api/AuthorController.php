<?php

namespace App\Controller\Api;

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthorController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var AuthorRepository
     */
    private $authorRepository;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param AuthorRepository $authorRepository
     * @param EntityManagerInterface $manager
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        AuthorRepository $authorRepository,
        EntityManagerInterface $manager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $manager;
        $this->serializer = $serializer;
        $this->authorRepository = $authorRepository;
        $this->validator = $validator;
    }

    /**
     * @Route("/api/authors", name="app_api_authors_list", methods={"GET"})
     */
    public function list(Request $request)
    {
        $params = $request->query->all();

        $authors = $this->authorRepository->findByFields($params);

        $jsonContent = $this->serializer->serialize(
            [
                'data' => $authors
            ],
            'json',
            [
                'groups' => [
                    'book',
                    'author',
                    'book_detail' /* if you add "author_detail" here you get circular reference */
                ]
            ]
        );

        return $this->json($jsonContent);
    }

    /**
     * @Route("/api/authors/create", name="app_api_authors_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $name = $request->get('name', "");

        $author = new Author();
        $author->setName($name);

        $errors = $this->validator->validate($author);

        if(!empty($errors)) {
            throw $this->createNotFoundException($errors->get(0)->getMessage());
        }

        $this->entityManager->persist($author);
        $this->entityManager->flush();

        $jsonContent = $this->serializer->serialize(
            [
                'data' => $author
            ],
            'json'
        );

        return $this->json($jsonContent, Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/authors/{id}", name="app_api_author_show", methods={"GET"})
     */
    public function show(Author $author): Response
    {
        $jsonContent = $this->serializer->serialize(
            [
                'data' => $author
            ],
            'json',
            [
                'groups' => [
                    'book',
                    'author',
                    'book_detail' /* if you add "author_detail" here you get circular reference */
                ]
            ]
        );

        return $this->json($jsonContent);
    }

    /**
     * @Route("/api/authors/{id}", name="app_api_author_update", methods={"PUT"})
     */
    public function update(Request $request, Author $author): Response
    {
        $author->setName($request->get('name', ""));

        $errors = (string) $this->validator->validate($author);

        if(!empty($errors)) {
            throw $this->createNotFoundException($errors);
        }

        $this->entityManager->persist($author);
        $this->entityManager->flush();

        $jsonContent = $this->serializer->serialize(
            [
                'data' => $author
            ],
            'json',
            [
                'groups' => [
                    'book',
                    'author',
                    'book_detail' /* if you add "author_detail" here you get circular reference */
                ]
            ]
        );

        return $this->json($jsonContent);
    }

    /**
     * @Route("/api/authors/{id}", name="app_api_author_destroy", methods={"DELETE"})
     */
    public function destroy(Author $author): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($author);
        $manager->flush();
        $data = [
            "errors" => false,
            'message' => 'Deleted'
        ];

        $jsonContent = $this->serializer->serialize($data, 'json');

        return $this->json($jsonContent);
    }
}