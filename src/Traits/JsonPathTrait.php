<?php

namespace App\Traits;

use Swaggest\JsonDiff\JsonPatch;
use Symfony\Component\HttpFoundation\Request;

trait JsonPathTrait
{
    /**
     * Return updated data in stdClass
     * @param object $entity
     * @param Request $request
     * @return object
     */
    protected function applyJsonPath(object $entity, Request $request): object
    {
        $original = json_decode($this->json($entity)->getContent());
        $patch = JsonPatch::import(json_decode($request->getContent()));
        $patch->apply($original);
        return $original;
    }
}