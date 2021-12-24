<?php

namespace App\Service;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class PaginatorService
{
    CONST ITEMS_PER_PAGE = 3;

    /**
     * @var PaginatorInterface
     */
    public $paginator;

    /**
     * @var Request
     */
    private $request;

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
        $this->request = Request::createFromGlobals();
    }

    public function getPaginate($booksQuery)
    {
        return $this->paginator->paginate(
            $booksQuery,
            $this->request->query->getInt('page', 1),
            self::ITEMS_PER_PAGE
        );
    }
}