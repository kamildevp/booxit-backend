<?php

declare(strict_types=1);

namespace App\Service\Auth\AccessRule;

use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use App\Exceptions\ForbiddenException;
use App\Exceptions\UnauthorizedException;
use App\Repository\OrganizationMemberRepository;
use App\Repository\OrganizationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class OrganizationManagementPrivilegesRule implements AccessRuleInterface
{
    public function __construct(
        protected OrganizationRepository $organizationRepository,
        protected OrganizationMemberRepository $organizationMemberRepository
    )
    {
        
    }

    public function validateAccess(?UserInterface $user, Request $request): void
    {
        if(!$user instanceof User){
            throw new UnauthorizedException;
        }

        $organizationId = $request->attributes->get('organization');
        $organization = $organizationId ? $this->organizationRepository->findOrFail($organizationId) : null;

        $actionMember = $organization ? $this->organizationMemberRepository->findOneBy([
            'organization' => $organization,
            'appUser' => $user,
        ]) : null;
        
        if(!$actionMember || $actionMember->getRole() != OrganizationRole::ADMIN->value){
            throw new ForbiddenException;
        }
    }
}