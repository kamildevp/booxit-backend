<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Symfony\Bundle\SecurityBundle\Security;

/** @property OrganizationMember $object */
class MemberRoleTask implements SetterTaskInterface
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
        if(!$organization){
            $this->object->setRoles($roles);
            return;
        }

        $adminCount = $organization->getAdmins()->count();

        if($adminCount === 1 && in_array('ADMIN', $memberRoles) && !in_array('ADMIN', $roles)){
            $memberId = $this->object->getId();
            throw new InvalidRequestException("Cannot remove ADMIN role from member with id = {$memberId}, organization needs to have at least one admin");
        }

        $this->object->setRoles($roles);
    }




}