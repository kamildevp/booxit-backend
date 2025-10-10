<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Compound;

use Attribute;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

#[Attribute]
class DateStringRequirements extends Compound
{
    public function __construct(protected bool $allowNull = false, mixed $options = null)
    {
        parent::__construct($options);
    }

    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(allowNull: $this->allowNull),
            new Assert\Date(
                message: 'Parameter must be date in format Y-m-d'
            )
        ];
    }
}