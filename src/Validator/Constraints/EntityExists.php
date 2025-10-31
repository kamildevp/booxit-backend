<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Validator\EntityExistsValidator;
use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class EntityExists extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public string $entityClass,
        public string $fieldName = 'id',
        public array $relatedTo = [],
        public array $commonRelations = [],
        public $message = '{{ entityClass }} does not exist',
        ?array $groups = null,
        mixed $payload = null,
    ) 
    {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy()
    {
        return EntityExistsValidator::class;
    }
}