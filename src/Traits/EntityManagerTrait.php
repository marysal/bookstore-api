<?php

namespace App\Traits;

use App\Entity\Book;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait EntityManagerTrait
{
    protected function setEntityRelations(
        object $entity,
        array $idsRelations = []
    ) : void {

        if(empty($idsRelations)) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                "The {{$this->entityName}} must contain at least one relation ID"
            );
        }

        foreach ($idsRelations as $id) {
            $id = (int)$id;
            $object = $this->entityManager->find($this->relationEntity, $id);
            $this->validate($object);
            //$entity->appendAuthor($object);
            $entity->{$this->entityAddRelationMethodName}($object);
        }
    }

    protected function saveToDb($entity, $op = "persist"): void
    {
        $this->entityManager->{$op}($entity);
        $this->entityManager->flush();
    }
}