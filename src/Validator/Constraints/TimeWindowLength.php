<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Validator\TimeWindowLengthValidator;
use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class TimeWindowLength extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public ?string $minLength = null,
        public ?string $maxLength = null,
        public string $minMessage = 'Time window cannot be shorter than {{ minLength }}.',
        public string $maxMessage = 'Time window cannot be longer than {{ minLength }}.',
        ?array $groups = null,
        mixed $payload = null,
    ) 
    {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy()
    {
        return TimeWindowLengthValidator::class;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}