<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PhoneConstraint extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = "Phone number {{ phone }} does not seem to be a valid phone";

    public function validatedBy()
    {
        return ApiPhoneValidator::class;
    }
}
