<?php

declare(strict_types=1);

namespace App\Service\Auth\AccessRule;

use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use App\Enum\Schedule\ScheduleAccessType;
use App\Exceptions\ForbiddenException;
use App\Exceptions\UnauthorizedException;
use App\Repository\OrganizationMemberRepository;
use App\Repository\OrganizationRepository;
use App\Repository\ScheduleAssignmentRepository;
use App\Repository\ScheduleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class ScheduleWritePrivilegesRule implements AccessRuleInterface
{
    public function __construct(
        protected OrganizationRepository $organizationRepository,
        protected OrganizationMemberRepository $organizationMemberRepository,
        protected ScheduleRepository $scheduleRepository,
        protected ScheduleAssignmentRepository $scheduleAssignmentRepository
    )
    {
        
    }

    public function validateAccess(?UserInterface $user, Request $request): void
    {
        if(!$user instanceof User){
            throw new UnauthorizedException;
        }

        $scheduleId = $request->attributes->get('schedule');
        $schedule = !is_null($scheduleId) ? $this->scheduleRepository->findOrFail($scheduleId) : null;
        $organization = $schedule?->getOrganization();

        $actionMember = $organization ? $this->organizationMemberRepository->findOneBy([
            'organization' => $organization,
            'appUser' => $user,
        ]) : null;

        $scheduleAssignment = $actionMember && $schedule ? $this->scheduleAssignmentRepository->findOneBy([
            'schedule' => $schedule,
            'organizationMember' => $actionMember,
        ]) : null;
        
        $scheduleAccessType = $scheduleAssignment?->getAccessType();
        
        if(!$actionMember || ($actionMember->getRole() != OrganizationRole::ADMIN->value && $scheduleAccessType != ScheduleAccessType::WRITE->value)){
            throw new ForbiddenException;
        }
    }
}