<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Compound;

use Attribute;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

#[Attribute]
class EnumSetRequirements extends Compound
{
    public function __construct(protected string $enumClass, mixed $options = null)
    {
        parent::__construct($options);
    }

    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Choice(
                multiple: true,
                min: 1,
                multipleMessage: 'One or more of the given values is invalid, allowed values: {{ choices }}.',
                callback: [$this->enumClass, 'values']
            )
        ];
    }
}