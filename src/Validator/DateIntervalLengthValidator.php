<?php

declare(strict_types=1);

namespace App\Validator;

use App\Service\Utils\DateTimeUtils;
use App\Validator\Constraints\DateIntervalLength;
use DateInterval;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Exception;

class DateIntervalLengthValidator extends ConstraintValidator
{
    public function __construct(private DateTimeUtils $dateTimeUtils)
    {
        
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof DateIntervalLength) {
            throw new UnexpectedTypeException($constraint, DateIntervalLength::class);
        }

        try{
            $interval = new DateInterval($value);
        }
        catch(Exception){
            return;
        }

        $min = new DateInterval($constraint->min);
        $max = new DateInterval($constraint->max);
        if($this->dateTimeUtils->compareDateIntervals($interval, $min) < 0){
            $this->context->buildViolation($constraint->minMessage)
                ->addViolation();
        }

        if($this->dateTimeUtils->compareDateIntervals($max, $interval) < 0){
            $this->context->buildViolation($constraint->maxMessage)
                ->addViolation();
        }
    }
}