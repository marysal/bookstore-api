<?php

namespace App\Enum;

use App\Entity\Author;
use App\Entity\Book;

class EntityGroupsEnum
{
    const ENTITY_BOOKS = "books";
    const ENTITY_AUTHORS = "authors";
    const ENTITY_ORDERS = "orders";
    const ENTITY_DELETED = "deleted";

    public static function getEntityGroupsList(): array
    {
        return [
            self::ENTITY_BOOKS => [
                'book',
                'author',
                'author_detail' /* if you add "book_detail" here you get circular reference */
            ],
            self::ENTITY_AUTHORS => [
                'book',
                'author',
                'book_detail' /* if you add "author_detail" here you get circular reference */
            ],
            self::ENTITY_ORDERS => [
                'order',
                'book',
                'book_order'
            ],
            self::ENTITY_DELETED => [
                "errors" => false,
                'message' => 'Deleted'
            ]
        ];
    }

    public static function getEntityInfoList(): array
    {
        return [
            self::ENTITY_BOOKS => [
                "relation" => Author::class,
                "name" => "book",
                "addRelationMethodName " => "appendAuthor"
            ],
            self::ENTITY_ORDERS => [
                "relation" =>  Book::class,
                "name" => "order",
                "addRelationMethodName " => "appendBookOrderList"
            ],
        ];
    }
}