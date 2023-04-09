<?php

namespace App\Service\GetterHelper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Getter
{
    const PUBLIC_ACCESS = 1;

    public function __construct(private int|string $accessRule = self::PUBLIC_ACCESS)
    {
        
    }




}