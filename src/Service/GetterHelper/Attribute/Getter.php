<?php

namespace App\Service\GetterHelper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Getter
{
    const PUBLIC_ACCESS = 1;
    const SNAKE_CASE = 2;
    const CAMMEL_CASE = 3;

    public function __construct(
        public int|string $accessRule = self::PUBLIC_ACCESS, 
        public ?string $format = null, 
        public array $groups = ['Default'], 
        public int|string $propertyNameAlias = self::SNAKE_CASE
        )
    {
        
    }




}