<?php

namespace App\Service\Auth\Attribute;

use App\Service\Auth\AccessRule\AuthenticatedRule;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class RestrictedAccess
{

    public function __construct(public string $accessRule = AuthenticatedRule::class)
    {
        
    }

}