<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Validator\DateIntervalLengthValidator;
use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class DateIntervalLength extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public string $min,
        public string $max,
        public $minMessage = 'Provided interval is too short',
        public $maxMessage = 'Provided interval is too long',
        ?array $groups = null,
        mixed $payload = null,
    ) 
    {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy()
    {
        return DateIntervalLengthValidator::class;
    }
}