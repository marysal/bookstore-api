<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiPhoneValidator extends ConstraintValidator
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiUrl;

    public function __construct(HttpClientInterface $client, string $apiKey, string $apiUrl)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
    }

    public function validate($value, Constraint $constraint)
    {
        $value = preg_replace('/\D/', '', $value);
        if (empty($value)){
            $this->context->addViolation("Phone cannot be empty");
        } else {
            $response = $this->client->request(
                'GET',
                $this->apiUrl,
                [
                    'query' => [
                        'access_key' => $this->apiKey,
                        'number' => $value,
                        'format' => 1 // JSON response
                    ]
                ]
            );

            $data = $response->toArray();
            if (!isset($data['valid'])) {
                $this->context->addViolation('Phone API validation error');
            }
            if (isset($data['valid']) && $data['valid'] === false) {
                /** @var PhoneConstraint $constraint */
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{phone}}', $value)
                    ->addViolation();
            }
        }
    }
}
