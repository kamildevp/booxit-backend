<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Compound;

use Attribute;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

#[Attribute]
class UsernameRequirements extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Regex(
                    pattern: '/^(?=.*\p{L})[\p{L}\d_]{4,20}$/i',
                    message: 'Username length must be from 4 to 20 characters, can contain letters,numbers, special characters(_) and must have at least one letter.'
                )
            ])
        ];
    }
}