<?php

declare(strict_types=1);

namespace App\Service\Auth\AccessRule;

use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use App\Enum\Schedule\ScheduleAccessType;
use App\Exceptions\ForbiddenException;
use App\Exceptions\UnauthorizedException;
use App\Repository\OrganizationMemberRepository;
use App\Repository\ReservationRepository;
use App\Repository\ScheduleAssignmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class ReservationWritePrivilegesRule implements AccessRuleInterface
{
    public function __construct(
        protected OrganizationMemberRepository $organizationMemberRepository,
        protected ScheduleAssignmentRepository $scheduleAssignmentRepository,
        protected ReservationRepository $reservationRepository,
    )
    {
        
    }

    public function validateAccess(?UserInterface $user, Request $request): void
    {
        if(!$user instanceof User){
            throw new UnauthorizedException;
        }

        $reservationId = $request->attributes->get('reservation');
        $reservation = !is_null($reservationId) ? $this->reservationRepository->findOrFail($reservationId) : null;

        $schedule = $reservation?->getSchedule();
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