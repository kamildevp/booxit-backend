<?php

namespace App\Service\GetterHelper\CustomAccessRule;

use App\Entity\User;

interface CustomAccessRuleInterface{

    public function validateAccess(?User $user, object $object):bool;
}