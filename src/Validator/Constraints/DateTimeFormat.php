<?php

namespace App\Validator\Constraints;

use App\Validator\DateTimeFormatValidator;
use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class DateTimeFormat extends Constraint
{
    public $message = 'Value must be in format {{ format }}';

    #[HasNamedArguments]
    public function __construct(
        public string $format,
        array $groups = null,
        mixed $payload = null,
    ) 
    {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy()
    {
        return DateTimeFormatValidator::class;
    }
}