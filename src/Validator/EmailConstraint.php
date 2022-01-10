<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\EmailValidator;

/**
 * @Annotation
 */
class EmailConstraint extends Constraint
{
    /*
    * Any public properties become valid options for the annotation.
    * Then, use these in your validator class.
    */
    public $message = "Email {{ email }} does not seem to be a valid email";

    public function validatedBy()
    {
        return EmailValidator::class;
    }
}