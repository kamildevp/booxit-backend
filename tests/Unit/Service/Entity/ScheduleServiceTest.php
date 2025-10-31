<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity;

use App\DTO\ScheduleService\ScheduleServiceAvailabilityGetDTO;
use App\Entity\Organization;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Exceptions\ConflictException;
use App\Exceptions\EntityNotFoundException;
use App\Repository\ScheduleRepository;
use App\Repository\ServiceRepository;
use App\Service\Entity\AvailabilityService;
use App\Service\Entity\ScheduleService;
use App\Service\Utils\DateTimeUtils;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScheduleServiceTest extends TestCase
{
    private MockObject&ScheduleRepository $scheduleRepositoryMock;
    private MockObject&ServiceRepository $serviceRepositoryMock;
    private MockObject&DateTimeUtils $dateTimeUtilsMock;
    private MockObject&AvailabilityService $availabilityServiceMock;
    private ScheduleService $scheduleService;

    protected function setUp(): void
    {
        $this->scheduleRepositoryMock = $this->createMock(ScheduleRepository::class);
        $this->serviceRepositoryMock = $this->createMock(ServiceRepository::class);
        $this->dateTimeUtilsMock = $this->createMock(DateTimeUtils::class);
        $this->availabilityServiceMock = $this->createMock(AvailabilityService::class);

        $this->scheduleService = new ScheduleService(
            $this->scheduleRepositoryMock,
            $this->serviceRepositoryMock,
            $this->dateTimeUtilsMock,
            $this->availabilityServiceMock
        );
    }

    public function testAddScheduleServiceAssignsServiceWhenNotAssigned(): void
    {
        $organizationMock = $this->createMock(Organization::class);
        $scheduleMock = $this->createMock(Schedule::class);
        $serviceMock = $this->createMock(Service::class);
        $serviceId = 1;

        $this->serviceRepositoryMock
            ->expects($this->once())
            ->method('findOrFail')
            ->with($serviceId)
            ->willReturn($serviceMock);

        $scheduleMock->method('getOrganization')->willReturn($organizationMock);
        $serviceMock->method('getOrganization')->willReturn($organizationMock);
        $scheduleMock->method('hasService')->with($serviceMock)->willReturn(false);

        $scheduleMock
            ->expects($this->once())
            ->method('addService')
            ->with($serviceMock);

        $this->scheduleRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($scheduleMock);

        $this->scheduleService->addScheduleService(
            $scheduleMock,
            $serviceId,
        );
    }

    public function testAddScheduleServiceThrowsConflictWhenServiceAlreadyAssigned(): void
    {
        $organizationMock = $this->createMock(Organization::class);
        $scheduleMock = $this->createMock(Schedule::class);
        $serviceId = 1;
        $serviceMock = $this->createMock(Service::class);

        $this->serviceRepositoryMock
            ->expects($this->once())
            ->method('findOrFail')
            ->with($serviceId)
            ->willReturn($serviceMock);

        $scheduleMock->method('getOrganization')->willReturn($organizationMock);
        $serviceMock->method('getOrganization')->willReturn($organizationMock);
        $scheduleMock->method('hasService')->with($serviceMock)->willReturn(true);

        $this->expectException(ConflictException::class);

        $this->scheduleService->addScheduleService(
            $scheduleMock,
            $serviceId,
        );
    }

    public function testRemoveScheduleServiceRemovesServiceWhenAssigned(): void
    {
        $scheduleMock = $this->createMock(Schedule::class);
        $serviceMock = $this->createMock(Service::class);

        $scheduleMock->method('hasService')->with($serviceMock)->willReturn(true);

        $scheduleMock
            ->expects($this->once())
            ->method('removeService')
            ->with($serviceMock);

        $this->scheduleRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($scheduleMock);

        $this->scheduleService->removeScheduleService(
            $scheduleMock,
            $serviceMock,
        );
    }

    public function testRemoveScheduleServiceThrowsNotFoundWhenServiceNotAssigned(): void
    {
        $scheduleMock = $this->createMock(Schedule::class);
        $serviceMock = $this->createMock(Service::class);

        $scheduleMock->method('hasService')->with($serviceMock)->willReturn(false);

        $this->expectException(EntityNotFoundException::class);

        $this->scheduleService->removeScheduleService(
            $scheduleMock,
            $serviceMock,
        );
    }

    public function testGetScheduleAvailabilityReturnsAvailabilityResult(): void
    {
        $scheduleMock = $this->createMock(Schedule::class);
        $serviceMock = $this->createMock(Service::class);
        $startDate = '2025-10-10';
        $endDate = '2025-10-12';
        $availabilityMock = ['2025-10-10' => [], '2025-10-11' => [], '2025-10-12' => ['11:00', '11:15']];

        $scheduleMock->method('hasService')->with($serviceMock)->willReturn(true);
        $startDateTime = new DateTimeImmutable($startDate);
        $endDateTime = new DateTimeImmutable($endDate);

        $this->dateTimeUtilsMock
            ->method('resolveDateTimeImmutableWithDefault')
            ->willReturnCallback(function($date, $default) use ($startDate, $endDate, $startDateTime, $endDateTime){
                switch(true){
                    case $date == $startDate && $default == new DateTimeImmutable('monday this week'):
                        return $startDateTime;
                    case $date == $endDate && $default == new DateTimeImmutable('sunday this week'):
                        return $endDateTime;
                }
            });

        $this->availabilityServiceMock
            ->expects($this->once())
            ->method('getScheduleAvailability')
            ->with($scheduleMock, $serviceMock, $startDateTime, $endDateTime)
            ->willReturn($availabilityMock);

        $result = $this->scheduleService->getScheduleAvailability($scheduleMock, $serviceMock, new ScheduleServiceAvailabilityGetDTO($startDate, $endDate));
        $this->assertEquals($availabilityMock, $result);
    }

    public function testGetScheduleAvailabilityThrowsNotFoundWhenServiceNotAssigned(): void
    {
        $scheduleMock = $this->createMock(Schedule::class);
        $serviceMock = $this->createMock(Service::class);

        $scheduleMock->method('hasService')->with($serviceMock)->willReturn(false);

        $this->expectException(EntityNotFoundException::class);

        $this->scheduleService->getScheduleAvailability($scheduleMock, $serviceMock, new ScheduleServiceAvailabilityGetDTO());
    }
}
