<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Compound;

use Attribute;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

#[Attribute]
class PhoneNumberRequirements extends Compound
{
    const FORMAT = 'Y-m-d\TH:iP';

    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Sequentially([
                new Assert\NotBlank(),
                new PhoneNumber()
            ])
        ];
    }
}