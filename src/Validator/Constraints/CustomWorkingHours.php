<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Validator\CustomWorkingHoursValidator;
use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class CustomWorkingHours extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public string $scheduleRouteParameter = 'schedule',
        public string $message = 'Provided working hours are overlapping.',
        public string $anotherDateOverlapMessage = 'Provided working hours are overlapping with custom hours for {{ date }}.',
        ?array $groups = null,
        mixed $payload = null,
    ) 
    {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy()
    {
        return CustomWorkingHoursValidator::class;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}