<?php

namespace App\Controller\Api;

use App\Service\ElasticSearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @var ElasticSearchService
     */
    protected $elasticSearch;

    /**
     * @param ElasticSearchService $elasticSearch
     */
    public function __construct(ElasticSearchService $elasticSearch)
    {
        $this->elasticSearch = $elasticSearch;
    }

    /**
     * @Route("/api/search", name="api_search", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        return $this->json(
            $this->elasticSearch->getBooksFromSearchResult(
                $this->elasticSearch->setElasticSearchParams($request)
            )
        );
    }
}
