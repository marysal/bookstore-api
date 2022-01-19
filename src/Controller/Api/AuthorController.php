<?php

namespace App\Controller\Api;

use App\Entity\Author;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class AuthorController extends BaseController
{
    protected static $entityName = "author";

    /**
     * @Route("/api/authors", name="app_api_authors_list", methods={"GET"})
     */
    public function list(Request $request)
    {
        $params = $request->query->all();

        $authors = $this->authorRepository->findByFields($params);

        return $this->response(
            $authors,
            $request->getAcceptableContentTypes()
        );
    }

    /**
     * @Route("/api/authors", name="app_api_authors_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $author = $this->serializer->deserialize(
            $request->getContent(),
            Author::class,
            $request->getContentType()
        );

        $this->validate($author);

        $this->entityNormalizer->saveToDb($author);

        return $this->response(
            $author,
            $request->getAcceptableContentTypes(),
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/api/authors/{id}", name="app_api_author_show", methods={"GET"})
     */
    public function show(Request $request, Author $author): Response
    {
        return $this->response(
            $author,
            $request->getAcceptableContentTypes()
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
            $request->getContentType(),
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $author
            ],
        );

        $this->validate($author);

        $this->entityNormalizer->saveToDb($author);

        return $this->response(
            $author,
            $request->getAcceptableContentTypes()
        );
    }

    /**
     * @Route("/api/authors/{id}", name="app_api_author_destroy", methods={"DELETE"})
     */
    public function destroy(Request $request, Author $author): Response
    {
        $this->entityNormalizer->removeFromDb($author);

        return $this->response(
            [],
            $request->getAcceptableContentTypes()
        );
    }
}