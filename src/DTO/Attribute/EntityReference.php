<?php

declare(strict_types=1);

namespace App\DTO\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EntityReference
{
    public function __construct(public string $entityClass, public ?string $alias = null){

    }
}
