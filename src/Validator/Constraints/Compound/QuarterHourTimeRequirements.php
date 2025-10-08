<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Compound;

use Attribute;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

#[Attribute]
class QuarterHourTimeRequirements extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Sequentially([
                new Assert\Time(withSeconds: false),
                new Assert\Regex(
                    pattern: '/^\d{2}:(00|15|30|45)$/',
                    message: 'Time must be in HH:MM format with minutes being 00, 15, 30, or 45.'
                ),
            ])
        ];
    }
}