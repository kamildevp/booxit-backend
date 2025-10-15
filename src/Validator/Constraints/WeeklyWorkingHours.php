<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Validator\WeeklyWorkingHoursValidator;
use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class WeeklyWorkingHours extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public string $message = 'Provided working hours are overlapping.',
        ?array $groups = null,
        mixed $payload = null,
    ) 
    {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy()
    {
        return WeeklyWorkingHoursValidator::class;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}