<?php

namespace App\Service;

use Swaggest\JsonDiff\JsonPatch;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class JsonService
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Return updated data in stdClass
     * @param object $entity
     * @param Request $request
     * @return object
     */
    public function applyJsonPatch(
        object $entity,
        Request $request,
        $entityName
    ): object {

        $original = $this->container->get('serializer')->serialize($entity, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], ['groups' => $entityName]));

        $original = (object) json_decode($original, true);
        $patch = JsonPatch::import(json_decode(json_decode($request->getContent()), true));
        $patch->apply($original);

        return $original;
    }
}