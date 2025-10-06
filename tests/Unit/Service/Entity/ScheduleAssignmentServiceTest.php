<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\Schedule;
use App\Entity\ScheduleAssignment;
use App\Enum\Schedule\ScheduleAccessType;
use App\Exceptions\ConflictException;
use App\Repository\ScheduleAssignmentRepository;
use App\Repository\OrganizationMemberRepository;
use App\Service\Entity\ScheduleAssignmentService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScheduleAssignmentServiceTest extends TestCase
{
    private MockObject&OrganizationMemberRepository $organizationMemberRepositoryMock;
    private MockObject&ScheduleAssignmentRepository $scheduleAssignmentRepositoryMock;
    private ScheduleAssignmentService $scheduleAssignmentService;

    protected function setUp(): void
    {
        $this->organizationMemberRepositoryMock = $this->createMock(OrganizationMemberRepository::class);
        $this->scheduleAssignmentRepositoryMock = $this->createMock(ScheduleAssignmentRepository::class);

        $this->scheduleAssignmentService = new ScheduleAssignmentService(
            $this->scheduleAssignmentRepositoryMock,
            $this->organizationMemberRepositoryMock,
        );
    }

    public function testCreateScheduleAssignmentCreatesNewWhenNotExisting(): void
    {
        $organizationMock = $this->createMock(Organization::class);
        $scheduleMock = $this->createMock(Schedule::class);
        $organizationMemberId = 1;
        $organizationMemberMock = $this->createMock(OrganizationMember::class);
        $accessType = ScheduleAccessType::READ;

        $this->organizationMemberRepositoryMock
            ->expects($this->once())
            ->method('findOrFail')
            ->with($organizationMemberId)
            ->willReturn($organizationMemberMock);

        $scheduleMock->method('getOrganization')->willReturn($organizationMock);
        $organizationMemberMock->method('getOrganization')->willReturn($organizationMock);

        $this->scheduleAssignmentRepositoryMock
            ->method('findOneBy')
            ->with([
                'schedule' => $scheduleMock,
                'organizationMember' => $organizationMemberMock
            ])
            ->willReturn(null);

        $this->scheduleAssignmentRepositoryMock
            ->expects($this->once())
            ->method('save');

        $scheduleAssignment = $this->scheduleAssignmentService->createScheduleAssignment(
            $scheduleMock,
            $organizationMemberId,
            $accessType
        );

        $this->assertInstanceOf(ScheduleAssignment::class, $scheduleAssignment);
        $this->assertSame($scheduleMock, $scheduleAssignment->getSchedule());
        $this->assertSame($organizationMemberMock, $scheduleAssignment->getOrganizationMember());
        $this->assertSame($accessType->value, $scheduleAssignment->getAccessType());
    }

    public function testCreateScheduleAssignmentThrowsConflictForInvalidOrganizationMember(): void
    {
        $organizationMock = $this->createMock(Organization::class);
        $organizationMock2 = $this->createMock(Organization::class);
        $scheduleMock = $this->createMock(Schedule::class);
        $organizationMemberId = 1;
        $organizationMemberMock = $this->createMock(OrganizationMember::class);
        $accessType = ScheduleAccessType::READ;

        $this->organizationMemberRepositoryMock
            ->expects($this->once())
            ->method('findOrFail')
            ->with($organizationMemberId)
            ->willReturn($organizationMemberMock);

        $scheduleMock->method('getOrganization')->willReturn($organizationMock);
        $organizationMemberMock->method('getOrganization')->willReturn($organizationMock2);

        $this->expectException(ConflictException::class);

        $this->scheduleAssignmentService->createScheduleAssignment(
            $scheduleMock,
            $organizationMemberId,
            $accessType
        );
    }

    public function testCreateScheduleAssignmentThrowsConflictForExistingAssignment(): void
    {
        $organizationMock = $this->createMock(Organization::class);
        $scheduleMock = $this->createMock(Schedule::class);
        $organizationMemberId = 1;
        $organizationMemberMock = $this->createMock(OrganizationMember::class);
        $accessType = ScheduleAccessType::READ;
        $scheduleAssignmentMock = $this->createMock(ScheduleAssignment::class);

        $this->organizationMemberRepositoryMock
            ->expects($this->once())
            ->method('findOrFail')
            ->with($organizationMemberId)
            ->willReturn($organizationMemberMock);

        $scheduleMock->method('getOrganization')->willReturn($organizationMock);
        $organizationMemberMock->method('getOrganization')->willReturn($organizationMock);

                $this->scheduleAssignmentRepositoryMock
            ->method('findOneBy')
            ->with([
                'schedule' => $scheduleMock,
                'organizationMember' => $organizationMemberMock
            ])
            ->willReturn($scheduleAssignmentMock);

        $this->expectException(ConflictException::class);

        $this->scheduleAssignmentService->createScheduleAssignment(
            $scheduleMock,
            $organizationMemberId,
            $accessType
        );
    }
}
