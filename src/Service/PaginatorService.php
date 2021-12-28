<?php

namespace App\Service;

use Knp\Component\Pager\PaginatorInterface;

class PaginatorService
{
    CONST ITEMS_PER_PAGE = 3;

    /**
     * @var PaginatorInterface
     */
    public $paginator;

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    public function getPaginate($booksQuery, int $page = 1)
    {
        return $this->paginator->paginate(
            $booksQuery,
            $page,
            self::ITEMS_PER_PAGE
        );
    }
}