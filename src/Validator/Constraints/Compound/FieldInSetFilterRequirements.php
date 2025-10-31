<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Compound;

use Attribute;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

#[Attribute]
class FieldInSetFilterRequirements extends Compound
{
    public function __construct(protected string $type, mixed $options = null)
    {
        parent::__construct($options);
    }

    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Sequentially([
                new Assert\Count(
                    min: 1,
                ),
                new Assert\All([
                    new Assert\Type($this->type)
                ])
            ])
        ];
    }
}