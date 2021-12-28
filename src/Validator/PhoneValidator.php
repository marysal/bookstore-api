<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PhoneValidator extends ConstraintValidator
{

    public function validate($phone, Constraint $constraint)
    {
        if(empty($phone)) {
            $this->context->addViolation("Phone can not be empty");
        } else if(!$this->isValidatePhoneNumber($phone)) {
            $this->context->addViolation("Invalid phone number");
        }
    }

    private function isValidatePhoneNumber(string $phone, int $minDigits = 9, int $maxDigits = 14): bool
    {
        if (preg_match('/^[+][0-9]/', $phone)) { //is the first character + followed by a digit
            $count = 1;
            $phone = str_replace(['+'], '', $phone, $count); //remove +
        }

        //remove white space, dots, hyphens and brackets
        $telephone = str_replace([' ', '.', '-', '(', ')'], '', $phone);

        //are we left with digits only?
        return $this->isDigits($telephone, $minDigits, $maxDigits);
    }

    private function isDigits(string $s, int $minDigits = 9, int $maxDigits = 14): bool
    {
        return preg_match('/^[0-9]{'.$minDigits.','.$maxDigits.'}\z/', $s);
    }
}