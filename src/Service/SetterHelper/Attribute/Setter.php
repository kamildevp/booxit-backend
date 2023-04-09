<?php

namespace App\Service\SetterHelper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Setter
{

    public function __construct(private ?string $setterTask = null, private ?string $targetParameter = null, private ?array $aliases = [])
    {
        
    }




}