<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Validator\DefinedVerificationHandlerValidator;
use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class DefinedVerificationHandler extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public $message = 'Invalid verification handler',
        ?array $groups = null,
        mixed $payload = null,
    ) 
    {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy()
    {
        return DefinedVerificationHandlerValidator::class;
    }
}