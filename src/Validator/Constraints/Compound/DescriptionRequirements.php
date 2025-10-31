<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Compound;

use Attribute;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

#[Attribute]
class DescriptionRequirements extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Length(
                max: 2000,
                maxMessage: 'Parameter cannot be longer than {{ limit }} characters',
            )
        ];
    }
}