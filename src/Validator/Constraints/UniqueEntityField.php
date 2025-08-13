<?php

namespace App\Validator\Constraints;

use App\Validator\UniqueEntityFieldValidator;
use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class UniqueEntityField extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public string $entityClass,
        public string $fieldName,
        public array $ignore = [],
        public $message = '{{ entityClass }} with provided {{ fieldName }} already exists',
        ?array $groups = null,
        mixed $payload = null,
    ) 
    {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy()
    {
        return UniqueEntityFieldValidator::class;
    }
}