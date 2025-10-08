<?php

declare(strict_types=1);

namespace App\Validator;

use App\Validator\Constraints\TimeWindow;
use DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TimeWindowValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof TimeWindow) {
            throw new UnexpectedTypeException($constraint, TimeWindow::class);
        }

        if (!is_object($value)) {
            return;
        }

        $objectProperties = get_object_vars($value);
        if(!isset($objectProperties[$constraint->startTimeProperty]) || !isset($objectProperties[$constraint->endTimeProperty])){
            return;
        }

        $start = DateTime::createFromFormat('H:i', $objectProperties[$constraint->startTimeProperty]);
        $end = DateTime::createFromFormat('H:i', $objectProperties[$constraint->endTimeProperty]);

        if($start === false || $end == false){
            return;
        }

        if ($start >= $end) {
            $this->context->buildViolation($constraint->message)->atPath('errors')->addViolation();
        }
    }
}