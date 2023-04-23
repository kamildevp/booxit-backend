<?php

namespace App\Validator;

use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Validator\Constraints\DateTimeFormat;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class DateTimeFormatValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof DateTimeFormat) {
            throw new UnexpectedTypeException($constraint, DateTimeFormat::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'string');

            // separate multiple types using pipes
            // throw new UnexpectedValueException($value, 'string|int');
        }


        if (!(new DataHandlingHelper)->validateDateTime($value, $constraint->format)) {
            // the argument must be a string or an object implementing __toString()
            $violationBuilder =$this->context->buildViolation($constraint->message)
                ->setParameter('{{ format }}', $constraint->format)
                ->addViolation();
        }
    }
}