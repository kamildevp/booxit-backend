<?php

namespace App\Service\GetterHelper\CustomAccessRule;

use App\Entity\User;
use App\Service\GetterHelper\CustomAccessRule\CustomAccessRuleInterface;

class EmailAccessRule implements CustomAccessRuleInterface
{
    /** @param User $object */
    public function validateAccess(?User $user, object $object):bool
    {
        if(!$user){
            return false;
        }

        if($object === $user){
            return true;
        }

        $organizations = $object->getOrganizations();
        $isOrganizationAdmin = $organizations->exists(function($key, $organization) use ($user){
            return $organization->hasMember($user) && $organization->getMember($user)->hasRoles(['ADMIN']);
        });

        return $isOrganizationAdmin;
    }

}