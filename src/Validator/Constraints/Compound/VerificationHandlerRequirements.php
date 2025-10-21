<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Compound;

use App\Validator\Constraints as CustomAssert;
use Attribute;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

#[Attribute]
class VerificationHandlerRequirements extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Sequentially([
                new Assert\NotBlank(),
                new CustomAssert\DefinedVerificationHandler()
            ])
        ];
    }
}