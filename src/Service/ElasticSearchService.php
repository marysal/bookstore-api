<?php

namespace App\Service;

use App\Entity\Book;
use Elasticsearch\ClientBuilder;
use Symfony\Component\HttpFoundation\Request;

class ElasticSearchService
{
    /**
     * @var \Elasticsearch\Client
     */
    protected $client;

    /**
     * @var ValidatorService
     */
    protected $validator;

    protected $searchParams = [
        'index'  => 'app',
        'type'   => 'book',
        'body' => [
            'query' => []
        ]
    ];

    public function __construct(ValidatorService $validator)
    {
        $this->client = ClientBuilder::create()->setHosts(["host" => $_ENV['ELASTIC_HOST']])->build();
        $this->validator = $validator;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function setElasticSearchParams(Request $request, int $bookId = null): array
    {
        if(!empty($bookId)) {
            $this->searchParams["body"]["query"]["bool"]["must"][0]["match"]["id"] = $bookId;
            return $this->searchParams;
        }

        $this->validator->elasticSearchValidate($request);

        $this->searchParams["body"]["query"]["bool"] = [];

        $x = 0;

        foreach (json_decode($request->getContent()) as $key => $value) {
            $this->searchParams["body"]["query"]["bool"]["must"][$x]["match"][$key] = $value;
            $x++;
        }

        return $this->searchParams;
    }

    /**
     * @return array
     */
    public function getBooksFromSearchResult(array $searchParams): array
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

        return $books;
    }


    /**
     * @param Request $request
     * @param object|array $book
     * @return bool
     */
    public function updateBookInElastic(Request $request, $book): bool
    {
        $searchParams = $this->setElasticSearchParams($request, $book->getId());

        $params = [
            'index' => 'app',
            'type'    => 'book',
            'body'  => [
                'doc' => [
                    'id' => $book->getId(),
                    'title' =>  $book->getTitle(),
                    'description' => $book->getDescription(),
                    'type' => $book->getType(),
                    'authors' => []
                ]
            ]
        ];

        foreach ($book->getAuthors() as $key => $author) {
            $params["body"]["doc"]["authors"][$key] = [
                'id' => $author->getId(),
                'name' => $author->getName()
            ];
        }

        $result = $this->client->search(
            $searchParams
        );

        $searchResult = $result['hits']['hits'] ?? [];

        if (!empty($searchResult) && is_array($searchResult)) {
            foreach ($searchResult as $key => $item) {
                //var_dump($item["_id"]);die();

                $params['id'] = $item["_id"];

                $this->client->update($params);
                break;
            }
        }

        return true;
    }

    /**
     * @param array|object $book
     * @return bool
     */
    public function saveBookToElastic($book): bool
    {
        $params = [
            'index' => 'app',
            'type'    => 'book',
            'body'  => [
                'id' => $book->getId(),
                'title' =>  $book->getTitle(),
                'description' => $book->getDescription(),
                'type' => $book->getType(),
                'authors' => []
            ]
        ];

        foreach ($book->getAuthors() as $key => $author) {
            $params["body"]["authors"][$key] = [
                'id' => $author->getId(),
                'name' => $author->getName()
            ];
        }

        $this->client->index($params);

        return true;
    }
}