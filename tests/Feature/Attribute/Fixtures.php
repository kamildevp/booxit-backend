<?php

declare(strict_types=1);

namespace App\Tests\Feature\Attribute;

use Attribute;

#[\Attribute(Attribute::TARGET_METHOD)]
class Fixtures
{
    public function __construct(public array $fixtures, public bool $append = true)
    {
        
    }
}