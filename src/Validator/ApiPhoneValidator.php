<?php

namespace App\Validator;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiPhoneValidator extends ConstraintValidator
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        if(empty($_ENV['NUM_VERIFY_API_URL']) || empty($_ENV['NUM_VERIFY_API_KEY'])) {
            throw new HttpException(
               Response::HTTP_INTERNAL_SERVER_ERROR,
                "There is no url or key to connect to numverify api"
            );
        }

        $this->client = $client;
    }

    public function validate($phone, Constraint $constraint)
    {
        if(empty($phone)) {
            $this->context->addViolation("Phone can not be empty");
        } else {
            $response = $this->client->request(
                'GET',
                $_ENV['NUM_VERIFY_API_URL'],
                [
                    'query' => [
                        'access_key' => $_ENV['NUM_VERIFY_API_KEY'],
                        'number' => $phone,
                        'format' => 1
                    ]
                ]
            );

            $data = $response->toArray();

            if (empty($data['valid'])) {
                /** @var PhoneConstraint $constraint */
                $this->context->buildViolation($constraint->message)
                    ->setParameter('phone', $phone)
                    ->addViolation();
            }

        }
    }


}
