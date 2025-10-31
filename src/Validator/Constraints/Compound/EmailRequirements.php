<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Compound;

use Attribute;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

#[Attribute]
class EmailRequirements extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Email(
                    message: 'Parameter is not a valid email'
                )
            ])
        ];
    }
}