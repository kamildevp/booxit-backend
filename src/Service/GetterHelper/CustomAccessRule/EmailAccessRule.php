<?php

namespace App\Service\GetterHelper\CustomAccessRule;

use App\Entity\User;
use App\Service\GetterHelper\CustomAccessRule\CustomAccessRuleInterface;

class EmailAccessRule implements CustomAccessRuleInterface
{

    public function validateAccess(?User $user, object $object):bool
    {
        return $object === $user;
    }
}