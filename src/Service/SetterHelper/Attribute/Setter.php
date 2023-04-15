<?php

namespace App\Service\SetterHelper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Setter
{

    public function __construct(
        public ?string $setterTask = null, 
        public ?string $targetParameter = null, 
        public ?array $aliases = [], 
        public array $groups = ['Default']
        )
    {
        
    }




}