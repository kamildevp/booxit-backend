<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity;

use App\Entity\Schedule;
use App\Entity\Service;
use App\Entity\Reservation;
use App\Model\TimeWindow;
use App\Repository\ReservationRepository;
use App\Repository\ServiceRepository;
use App\Service\Entity\AvailabilityService;
use App\Service\Entity\WorkingHoursService;
use App\Service\Utils\DateTimeUtils;
use App\Tests\Unit\Service\Entity\DataProvider\AvailabilityServiceDataProvider;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AvailabilityServiceTest extends TestCase
{
    private ReservationRepository&MockObject $reservationRepositoryMock;
    private WorkingHoursService&MockObject $workingHoursServiceMock;
    private DateTimeUtils&MockObject $dateTimeUtilsMock;
    private AvailabilityService $availabilityService;
    private DateTimeZone $defaultTimezone;

    protected function setUp(): void
    {
        $this->reservationRepositoryMock = $this->createMock(ReservationRepository::class);
        $this->workingHoursServiceMock = $this->createMock(WorkingHoursService::class);
        $this->dateTimeUtilsMock = $this->createMock(DateTimeUtils::class);
        $this->defaultTimezone = new DateTimeZone('UTC');

        $this->availabilityService = new AvailabilityService(
            $this->reservationRepositoryMock,
            $this->workingHoursServiceMock,
            $this->dateTimeUtilsMock,
            $this->defaultTimezone->getName()
        );
    }

    #[DataProviderExternal(AvailabilityServiceDataProvider::class, 'getScheduleAvailabilityDataCases')]
    public function testGetScheduleAvailabilityReturnsExpectedAvailability(
        array $workingHoursTimeWindows,
        DateTimeZone $timezone,
        array $reservationsTimeWindows,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        int $scheduleDivision,
        int $availabilityOffset,
        array $availableTimeWindowsResult,
        array $serviceFilterResults,
        array $expectedResult
    ): void
    {
        $scheduleMock = $this->createMock(Schedule::class);
        $scheduleMock->method('getDivision')->willReturn($scheduleDivision);

        $reservationsMock = array_map(function($timeWindow){
            $reservationMock = $this->createMock(Reservation::class);
            $reservationMock->method('getStartDateTime')->willReturn($timeWindow->getStartTime());
            $reservationMock->method('getEndDateTime')->willReturn($timeWindow->getEndTime());
            return $reservationMock;
        }, $reservationsTimeWindows);

        $this->workingHoursServiceMock->method('getScheduleWorkingHoursForDateRange')
            ->with(
                $scheduleMock,
                $this->callback(fn($arg) => $arg instanceof DateTimeInterface && $arg >= $startDate->setTime(0,0)),
                $endDate->setTime(23,59),
                $timezone
            )
        ->willReturn($workingHoursTimeWindows);

        $this->reservationRepositoryMock->method('getActiveScheduleReservations')
            ->with(
                $scheduleMock,
                $this->callback(fn($arg) => $arg == $startDate->setTime(0,0) && $arg->getTimezone() == $this->defaultTimezone),
                $this->callback(fn($arg) => $arg == $endDate->setTime(23,59) && $arg->getTimezone() == $this->defaultTimezone),
            )
            ->willReturn($reservationsMock);

        $timeWindowsMergedMock = [new TimeWindow(new DateTime, new DateTime)];

        $this->dateTimeUtilsMock
            ->method('timeWindowCollectionDiff')
            ->willReturnCallback(fn($arg1) => match($arg1){
                $workingHoursTimeWindows => $workingHoursTimeWindows,
                $timeWindowsMergedMock => $availableTimeWindowsResult
            });

        $this->dateTimeUtilsMock
            ->method('mergeAdjacentTimeWindows')
            ->with($workingHoursTimeWindows)
            ->willReturn($timeWindowsMergedMock);

        $serviceMock = $this->createMock(Service::class);
        $serviceMock->method('getDuration')->willReturn(new DateInterval('PT30M'));
        $serviceMock->method('getAvailabilityOffset')->willReturn($availabilityOffset);

        $this->dateTimeUtilsMock
            ->method('compareDateIntervals')
            ->willReturnOnConsecutiveCalls(...$serviceFilterResults);

        $result = $this->availabilityService->getScheduleAvailability(
            $scheduleMock,
            $serviceMock,
            $startDate,
            $endDate,
            $timezone
        );

        $this->assertEquals($expectedResult, $result);
    }
}
