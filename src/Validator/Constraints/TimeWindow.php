<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Validator\TimeWindowValidator;
use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class TimeWindow extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public string $startTimeProperty = 'startTime',
        public string $endTimeProperty = 'endTime',
        public $message = 'Start time must be earlier than end time.',
        ?array $groups = null,
        mixed $payload = null,
    ) 
    {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy()
    {
        return TimeWindowValidator::class;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}