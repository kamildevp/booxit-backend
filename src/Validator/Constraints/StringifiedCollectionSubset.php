<?php

namespace App\Validator\Constraints;

use App\Validator\DateTimeFormatValidator;
use App\Validator\StringifiedCollectionSubsetValidator;
use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class StringifiedCollectionSubset extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public array $baseCollection = [],
        public ?string $baseCollectionCallbackMethod = null,
        public $message = 'Invalid collection',
        public string $separator = ',',
        public bool $allowEmpty = false,
        ?array $groups = null,
        mixed $payload = null,
    ) 
    {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy()
    {
        return StringifiedCollectionSubsetValidator::class;
    }
}