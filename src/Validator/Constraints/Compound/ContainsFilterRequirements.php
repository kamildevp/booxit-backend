<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Compound;

use Attribute;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

#[Attribute]
class ContainsFilterRequirements extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Length(
                min: 1,
                max: 50,
                minMessage: 'Parameter must be at least {{ limit }} characters long',
                maxMessage: 'Parameter cannot be longer than {{ limit }} characters',
            )
        ];
    }
}