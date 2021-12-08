<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;

class EditEntityService
{


    public function changeBook(
        Book $book,
        string $title,
        string $description,
        string $type
        //array $authors
    ) {
        $authors_id = [];
      /*  if(!empty($authors)) {
            array_map(function (Author $author) use ($book) {
                $book->appendAuthor($author);
            }, $authors);
        }*/


        if (!empty($title)) {
            $book->setTitle($title);
        }

        if(!empty($description)) {
            $book->setDescription($description);
        }

        if(!empty($type)) {
            $book->setType($type);
        }

      /*  if (!empty($authors_id)) {
            foreach ($authors_id as $author) {
                array_map(function (Author $author) use ($book) {
                    $book->appendAuthor($author);
                }, $authorRepository->find($author['id']));
            }

        }*/

        //var_dump($book->getTitle());die();

        return $book;
    }
}