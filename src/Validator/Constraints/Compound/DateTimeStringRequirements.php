<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Compound;

use Attribute;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

#[Attribute]
class DateTimeStringRequirements extends Compound
{
    const FORMAT = 'Y-m-d\TH:iP';

    public function __construct(protected bool $allowNull = false, mixed $options = null)
    {
        parent::__construct($options);
    }

    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Sequentially([
                new Assert\NotBlank(allowNull: $this->allowNull),
                new Assert\DateTime(
                    format: self::FORMAT, 
                    message: 'Parameter must be datetime string in format '. self::FORMAT
                )
            ])
        ];
    }
}