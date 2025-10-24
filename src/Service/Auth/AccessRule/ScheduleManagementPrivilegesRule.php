<?php

declare(strict_types=1);

namespace App\Service\Auth\AccessRule;

use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use App\Exceptions\ForbiddenException;
use App\Exceptions\UnauthorizedException;
use App\Repository\OrganizationMemberRepository;
use App\Repository\ScheduleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class ScheduleManagementPrivilegesRule implements AccessRuleInterface
{
    public function __construct(
        protected OrganizationMemberRepository $organizationMemberRepository,
        protected ScheduleRepository $scheduleRepository
    )
    {
        
    }

    public function validateAccess(?UserInterface $user, Request $request): void
    {
        if(!$user instanceof User){
            throw new UnauthorizedException;
        }

        $scheduleId = $request->attributes->get('schedule');
        $organization = !is_null($scheduleId) ? $this->scheduleRepository->findOrFail($scheduleId)->getOrganization() : null;

        $actionMember = $organization ? $this->organizationMemberRepository->findOneBy([
            'organization' => $organization,
            'appUser' => $user,
        ]) : null;
        
        if(!$actionMember || $actionMember->getRole() != OrganizationRole::ADMIN->value){
            throw new ForbiddenException;
        }
    }
}