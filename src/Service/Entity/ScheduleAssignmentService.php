<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\Repository\OrganizationMemberRepository;
use App\Entity\Schedule;
use App\Entity\ScheduleAssignment;
use App\Enum\Schedule\ScheduleAccessType;
use App\Exceptions\ConflictException;
use App\Repository\ScheduleAssignmentRepository;

class ScheduleAssignmentService
{
    public function __construct(
        protected ScheduleAssignmentRepository $scheduleAssignmentRepository,
        protected OrganizationMemberRepository $organizationMemberRepository,
    )
    {

    }

    public function createScheduleAssignment(Schedule $schedule, int $organizationMemberId, ScheduleAccessType $accessType): ScheduleAssignment
    {
        $organizationMember = $this->organizationMemberRepository->findOrFail($organizationMemberId);

        $existingScheduleAssignment = $this->scheduleAssignmentRepository->findOneBy([
            'schedule' => $schedule,
            'organizationMember' => $organizationMember
        ]);

        if($existingScheduleAssignment){
            throw new ConflictException('This organization member is already assigned to this schedule.');
        }

        $scheduleAssignment = new ScheduleAssignment();
        $scheduleAssignment->setSchedule($schedule);
        $scheduleAssignment->setOrganizationMember($organizationMember);
        $scheduleAssignment->setAccessType($accessType->value);
        $this->scheduleAssignmentRepository->save($scheduleAssignment, true);

        return $scheduleAssignment;
    }
}