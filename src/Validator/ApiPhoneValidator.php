<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiPhoneValidator extends ConstraintValidator
{
    private const NUM_VERIFY_API_URL = "http://apilayer.net/api/validate";

    private const NUM_VERIFY_API_KEY = "d16d7006aff743dc2cd293c3a4c44be0";

    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function validate($value, Constraint $constraint)
    {
        $phone = preg_replace('/[^0-9]/', '', $value);
        if(empty($phone)) {
            $this->context->addViolation("Phone can not be empty");
        } else {
            $response = $this->client->request(
                'GET',
                self::NUM_VERIFY_API_URL,
                [
                    'query' => [
                        'access_key' => self::NUM_VERIFY_API_KEY,
                        'number' => $phone,
                        'format' => 1
                    ]
                ]
            );

            $data = $response->toArray();
            if (empty($data['valid'])) {
                /** @var PhoneConstraint $constraint */
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{phone}}', $phone)
                    ->addViolation();
            }
        }
    }
}