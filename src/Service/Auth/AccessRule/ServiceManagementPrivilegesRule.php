<?php

declare(strict_types=1);

namespace App\Service\Auth\AccessRule;

use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use App\Exceptions\ForbiddenException;
use App\Exceptions\UnauthorizedException;
use App\Repository\OrganizationMemberRepository;
use App\Repository\OrganizationRepository;
use App\Repository\ServiceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class ServiceManagementPrivilegesRule implements AccessRuleInterface
{
    public function __construct(
        protected OrganizationRepository $organizationRepository,
        protected OrganizationMemberRepository $organizationMemberRepository,
        protected ServiceRepository $serviceRepository
    )
    {
        
    }

    public function validateAccess(?UserInterface $user, Request $request): void
    {
        if(!$user instanceof User){
            throw new UnauthorizedException;
        }

        $requestContent = json_decode($request->getContent(), true) ?? [];
        $organizationId = $requestContent['organization_id'] ?? null;
        $serviceId = $request->attributes->get('service');
        $organization = !is_null($serviceId) ? $this->serviceRepository->findOrFail($serviceId)->getOrganization() : null;
        $organization = !$organization && is_int($organizationId) ? $this->organizationRepository->find($organizationId) : $organization;

        $actionMember = $organization ? $this->organizationMemberRepository->findOneBy([
            'organization' => $organization,
            'appUser' => $user,
        ]) : null;
        
        if(!$actionMember || $actionMember->getRole() != OrganizationRole::ADMIN->value){
            throw new ForbiddenException;
        }
    }
}