<?php

namespace App\Service;

use App\Enum\EntityGroupsEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class EntityNormalizer extends ObjectNormalizer
{
    /**
     * Entity manager
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * Entity normalizer
     * @param EntityManagerInterface $em
     * @param ClassMetadataFactoryInterface|null $classMetadataFactory
     * @param NameConverterInterface|null $nameConverter
     * @param PropertyAccessorInterface|null $propertyAccessor
     */
    public function __construct(
        EntityManagerInterface $em,
        ?ClassMetadataFactoryInterface $classMetadataFactory = null,
        ?NameConverterInterface $nameConverter = null,
        ?PropertyAccessorInterface $propertyAccessor = null
    ) {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor);
        $this->em = $em;
    }

    public function setEntityRelations($entityName, $entity, array $idsRelations = []) : void
    {
        $entityInfo = EntityGroupsEnum::getEntityInfoList()[$entityName];

        //todo make a validation service and move the generation of exceptions there
        if(empty($entityInfo)) {
            throw new HttpException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                "There is no information about this entity {{$entityName}}"
            );
        }

        //todo make a validation service and move the generation of exceptions there
        if(empty($idsRelations)) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                "The {{$entityInfo["name"]}} must contain at least one relation ID"
            );
        }

        foreach ($idsRelations as $id) {
            $id = (int)$id;
            $object = $this->em->find($entityInfo["relation"], $id);

            //todo make a validation service and move the generation of exceptions there
            if (empty($object)) {
                throw new HttpException(
                    Response::HTTP_BAD_REQUEST,
                    "Object with this ID not found"
                );
            }

            $entity->{$entityInfo["addRelationMethodName"]}($object);
        }
    }

    public function saveToDb($entity): void
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function removeFromDb($entity): void
    {
        $this->em->remove($entity);
        $this->em->flush();
    }
}