<?php

declare(strict_types=1);

namespace App\Service\Auth\AccessRule;

use App\Entity\User;
use App\Enum\Organization\OrganizationTier;
use App\Exceptions\ConflictException;
use App\Exceptions\UnauthorizedException;
use App\Repository\OrganizationRepository;
use App\Repository\ScheduleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class OrganizationScheduleCountRule implements AccessRuleInterface
{
    const MAX_SCHEDULES_COUNT = 3;

    public function __construct(
        protected OrganizationRepository $organizationRepository,
        protected ScheduleRepository $scheduleRepository
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
        if(!$organization || $organization->getTier() == OrganizationTier::PREMIUM->value){
            return;
        }

        $schedulesCount = $this->scheduleRepository->getOrganizationSchedulesCount((int)$organizationId);
        if($schedulesCount >= self::MAX_SCHEDULES_COUNT){
            throw new ConflictException("The organization has already reached its maximum allowed number of schedules.");
        }
    }
}