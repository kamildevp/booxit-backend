<?php

declare(strict_types=1);

namespace App\Repository\ORM\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Verifiable
{
    public function __construct(public string $fieldName = 'verified')
    {
        
    }
}