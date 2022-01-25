<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorService
{
    const elasticSearchParams = [
        'id',
        'title',
        'type',
        'author',
    ];

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function elasticSearchValidate(Request $request): bool
    {
       $params = json_decode($request->getContent(), true);

       if (empty(count($params))) {
           throw new HttpException(
               Response::HTTP_BAD_REQUEST,
               "At least one search parameter is required: " .
               implode(", ", self::elasticSearchParams)
           );
       }

       $optional = [];

       foreach ($params as $key => $param) {
           if(!in_array($key, self::elasticSearchParams)) {
               throw new HttpException(
                   Response::HTTP_BAD_REQUEST,
                   "This parameter is not allowed to search: " . $key
               );
           }

           switch ($key) {
               case 'id':
                   $optional[$key] = new Assert\Type('int');
                   break;
               case 'title':
               case 'author':
                   $optional[$key] = new Assert\Length(['min' => 5, 'max' => 255]);
                   break;
               case 'type':
                   $optional[$key] =  new Assert\Choice(["choices" => ["prose", "poetry"], "message" => "You can choose 'prose' or 'poetry'"]);
                   break;
           }
       }

       $constraint = new Assert\Collection($optional);

       $errors = $this->validator->validate($params, $constraint);

        if ($errors->has(0)) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                $errors->get(0)->getPropertyPath(). ": ".$errors->get(0)->getMessage()
            );
        }

        return true;
    }
}