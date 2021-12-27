<?php

namespace App\Controller\Api;

use App\Entity\Author;
use App\Enum\EntityGroupsEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class AuthorController extends BaseController
{
    /**
     * @Route("/api/authors", name="app_api_authors_list", methods={"GET"})
     */
    public function list(Request $request)
    {
        $params = $request->query->all();

        $authors = $this->authorRepository->findByFields($params);

        return $this->json(
            $this->getJsonContent(
                $this->getJsonContent($authors, EntityGroupsEnum::ENTITY_AUTHORS)
            )
        );
    }

    /**
     * @Route("/api/authors/create", name="app_api_authors_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $author = $this->serializer->deserialize(
            $request->getContent(),
            Author::class,
            'json'
        );

        $errors = $this->validator->validate($author);

        if ($errors->has(0)) {
            throw $this->createNotFoundException(
                $errors->get(0)->getMessage()
            );
        }

        $this->entityManager->persist($author);
        $this->entityManager->flush();

        return $this->json(
            $this->getJsonContent($author, EntityGroupsEnum::ENTITY_AUTHORS),
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/api/authors/{id}", name="app_api_author_show", methods={"GET"})
     */
    public function show(Author $author): Response
    {
        return $this->json(
            $this->getJsonContent($author)
        );
    }

    /**
     * @Route("/api/authors/{id}", name="app_api_author_update", methods={"PUT"})
     */
    public function update(Request $request, Author $author): Response
    {
        $author = $this->serializer->deserialize(
            $request->getContent(),
            Author::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $author
            ],
        );

        $this->validate($author);

        $this->entityManager->persist($author);
        $this->entityManager->flush();

        return $this->json(
            $this->getJsonContent($author, EntityGroupsEnum::ENTITY_AUTHORS)
        );
    }

    /**
     * @Route("/api/authors/{id}", name="app_api_author_destroy", methods={"DELETE"})
     */
    public function destroy(Author $author): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($author);
        $manager->flush();

        return $this->json(
            $this->getJsonContent([], EntityGroupsEnum::ENTITY_DELETED)
        );
    }
}