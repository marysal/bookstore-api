<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class ConvertorService
{
    /**
     * @param Request $request
     * @return Request
     */
    public static function xml2Request(Request $request): Request
    {
        $xml = simplexml_load_string($request->getContent());
        $array =  self::object2array($xml);
        $request->attributes->set('status', $array['status'] ?? null);
        $request->attributes->set('email', $array['email'] ?? null);
        return $request;
    }

    public static function object2array($object) {
        return @json_decode(@json_encode($object),1);
    }
}