<?php

namespace App\Service\SetterHelper\Task\OrganizationMember;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Symfony\Bundle\SecurityBundle\Security;

/** @property OrganizationMember $object */
class RoleTask implements SetterTaskInterface
{
    use SetterTaskTrait;
    
    private User $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function runPreValidation(array $roles)
    {
        $roles = array_unique($roles);
        foreach($roles as $role){
            if(!in_array($role, Organization::ALLOWED_ROLES)){
                $allowedRolesString = join(', ', Organization::ALLOWED_ROLES);
                $this->validationErrors['roles'] = "Role {$role} is not valid member role. Allowed roles: {$allowedRolesString}";
                return;
            }
        }

        $memberRoles = $this->object->getRoles();  
        $organization = $this->object->getOrganization();
        $adminCount = $organization->getAdmins()->count();

        if($adminCount === 1 && in_array('ADMIN', $memberRoles) && !in_array('ADMIN', $roles)){
            $memberId = $this->object->getId();
            $this->requestErrors['roles'] = "Cannot remove ADMIN role from member with id = {$memberId}, organization needs to have at least one admin";
            return;
        }

        $this->object->setRoles($roles);
    }




}