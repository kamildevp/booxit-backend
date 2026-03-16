<?php

declare(strict_types=1);

namespace App\Serializer\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ResourceUrl
{
    public function __construct(
        public string $routeName, 
        public array $parameters = []
    )
    {

    }
}
