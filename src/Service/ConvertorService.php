<?php

namespace App\Service;

use App\Entity\Book;

class ConvertorService
{
    public static function convertBookObjectToArray($books): array
    {
        $response = [];
        if(is_array($books) && !empty($books)) {
            foreach ($books as $idBook => $book) {
                self::saveResponse($response, $book, $idBook);
            }
        } else {
            self::saveResponse($response, $books);
        }
        return $response;
    }

    private static function saveResponse(array &$response, Book $book, $idBook = 1)
    {
        $response[$idBook]['id'] = $book->getId();
        $response[$idBook]['title'] = $book->getTitle();
        $response[$idBook]['description'] = $book->getDescription();
        $response[$idBook]['type'] = $book->getType();
        $response[$idBook]['createdAt'] = $book->getCreatedAt();
        $response[$idBook]['updatedAt'] = $book->getUpdatedAt();
        if(!empty($authors = $book->getAuthors())) {
            foreach ($authors as $idAuthor => $author) {
                $response[$idBook]['authors'][$idAuthor]['id'] = $author->getId();
                $response[$idBook]['authors'][$idAuthor]['name'] = $author->getName();
            }
        }
    }
}