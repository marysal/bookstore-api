<?php

namespace App\Service;

use Swaggest\JsonDiff\JsonPatch;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\HttpFoundation\Request;

class JsonService
{
    use ControllerTrait;

    /**
     * Return updated data in stdClass
     * @param object $entity
     * @param Request $request
     * @return object
     */
    public function applyJsonPatch(object $entity, Request $request): object
    {
        $original = json_decode($this->json($entity)->getContent());
        $patch = JsonPatch::import(json_decode($request->getContent()));
        $patch->apply($original);
        return $original;
    }
}