<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Compound;

use Attribute;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

#[Attribute]
class DecimalRequirements extends Compound
{
    public function __construct(protected bool $allowNull = false, mixed $options = null)
    {
        parent::__construct($options);
    }

    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Sequentially([
                new Assert\NotBlank(allowNull: $this->allowNull),
                new Assert\Regex(
                    pattern: '/^\d+(\.\d{1,2})?$/',
                    message: 'Parameter must be a valid number with up to 2 decimals.'
                ),
                new Assert\Range(
                    min: 0,
                    max: 999999.99,
                    notInRangeMessage: 'Parameter must be between {{ min }} and {{ max }}.',
                ),
            ])
        ];
    }
}