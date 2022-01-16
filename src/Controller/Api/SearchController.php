<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends BaseController
{
    protected $searchParams = [
        'index'  => 'app',
        'type'   => 'book',
        'body' => [
            'query' => []
        ]
    ];

    /**
     * @Route("/api/search", name="api_search", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $this->setElasticSearchParams($request);

        return $this->json(
            $this->getBooksFromSearchResult()
        );
    }

    /**
     * @param Request $request
     */
    private function setElasticSearchParams(Request $request): void
    {
        $this->searchParams["body"]["query"]["bool"] = [];

        $x = 0;

        foreach (json_decode($request->getContent()) as $key => $value) {
            $this->searchParams["body"]["query"]["bool"]["must"][$x]["match"][$key] = $value;
            $x++;
        }

    }

    /**
     * @return array
     */
    protected function getBooksFromSearchResult(): array
    {
        $books = [];

        $searchResult = $this->client->search(
           $this->searchParams
        );

        $searchResult = $searchResult['hits']['hits'] ?? [];

        if (!empty($searchResult) && is_array($searchResult)) {
            foreach ($searchResult as $key => $item) {
                $books[$key]['id'] = $item["_source"]["id"];
                $books[$key]['title'] = $item["_source"]["title"];
                $books[$key]['description'] = $item["_source"]["description"];
                $books[$key]['type'] = $item["_source"]['type'];

                if (!empty($item["_source"]['authors'])) {
                    foreach ($item["_source"]['authors'] as $keyAuthor => $author) {
                        $books[$key]["authors"][$keyAuthor] = $author;
                    }
                }
            }
        }

        if (empty($books)) {
            throw $this->createNotFoundException('Not Found');
        }

        return $books;
    }
}
