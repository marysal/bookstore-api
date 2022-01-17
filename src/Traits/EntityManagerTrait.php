<?php

namespace App\Traits;

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
            $entity->{$this->entityAddRelationMethodName}($object);
        }
    }

    protected function saveToDb($entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    protected function removeFromDb($entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}