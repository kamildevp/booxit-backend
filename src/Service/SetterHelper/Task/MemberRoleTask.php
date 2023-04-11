<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Symfony\Bundle\SecurityBundle\Security;

/** @property OrganizationMember $object */
class MemberRoleTask implements SetterTaskInterface
{
    use SetterTaskTrait;
    const ALLOWED_ROLES = ['MEMBER', 'ADMIN'];
    const MODIFICATION_TYPES = ['ADD', 'REMOVE', 'OVERWRITE'];
    private User $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function runPreValidation(array $roles, string $rolesModificationType)
    {
        if(!in_array($rolesModificationType, self::MODIFICATION_TYPES)){
            $modificationTypesString = join(', ', self::MODIFICATION_TYPES);
            throw new InvalidRequestException("Invalid roles modification type. Allowed modification types: {$modificationTypesString}");
        }

        foreach($roles as $role){
            if(!in_array($role, self::ALLOWED_ROLES)){
                $allowedRolesString = join(', ', self::ALLOWED_ROLES);
                throw new InvalidRequestException("Role {$role} is not valid role. Allowed roles: {$allowedRolesString}");
            }
        }

        $memberRoles = $this->object->getRoles();
        $rolesDiff = array_diff($roles, $memberRoles);
        $rolesIntersect = array_intersect($roles, $memberRoles);
        
        switch(true){
            case $rolesModificationType === 'OVERWRITE':
                $modifiedRoles = $roles;
                break;
            case $rolesModificationType === 'REMOVE' && !empty($rolesDiff):
                $invalidRole = array_values($rolesDiff)[0];
                throw new InvalidRequestException("Member does not have {$invalidRole} role");
                break;
            case $rolesModificationType === 'REMOVE':
                $modifiedRoles = array_diff($memberRoles, $roles);
                break;
            case $rolesModificationType === 'ADD' && !empty($rolesIntersect):
                $invalidRole = array_values($rolesIntersect)[0];
                throw new InvalidRequestException("Member already has {$invalidRole} role");
                break;
            case $rolesModificationType === 'ADD':
                $modifiedRoles = array_merge($memberRoles, $roles);
                break;
        }

        $organization = $this->object->getOrganization();
        $members = $organization->getMembers();
        $adminCount = $members->filter(function($member){
            return $member->hasRoles(['ADMIN']);
        })->count();

        if($adminCount === 1 && in_array('ADMIN', $memberRoles) && !in_array('ADMIN', $modifiedRoles)){
            throw new InvalidRequestException('Cannot remove ADMIN role from member. Organization needs to have at least one admin');
        }

        $this->object->setRoles($modifiedRoles);
    }




}