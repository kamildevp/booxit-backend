<?php

declare(strict_types=1);

namespace App\Repository\Filter\EntityFilter\Attribute;

use Attribute;
use App\Repository\Filter\EntityFilter\EntityFilterInterface;

#[Attribute(Attribute::TARGET_PROPERTY|Attribute::IS_REPEATABLE)]
class Filter
{

    public function __construct(
        public string $parameterName,
        public EntityFilterInterface $entityFilter,
    )
    {
        
    }
}