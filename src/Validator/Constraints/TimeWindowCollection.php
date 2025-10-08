<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Validator\TimeWindowCollectionValidator;
use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class TimeWindowCollection extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public string $startTimeProperty = 'startTime',
        public string $endTimeProperty = 'endTime',
        public $message = 'Invalid timewindow collection.',
        ?array $groups = null,
        mixed $payload = null,
    ) 
    {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy()
    {
        return TimeWindowCollectionValidator::class;
    }
}