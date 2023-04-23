<?php

namespace App\Validator\Constraints;

use App\Validator\ReservationValidator;
use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class Reservation extends Constraint
{
    public string $message = 'Reservation time window is not available';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return ReservationValidator::class;
    }
}