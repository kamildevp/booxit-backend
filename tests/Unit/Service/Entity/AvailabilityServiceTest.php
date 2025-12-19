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
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AvailabilityServiceTest extends TestCase
{
    private ReservationRepository&MockObject $reservationRepositoryMock;
    private WorkingHoursService&MockObject $workingHoursServiceMock;
    private DateTimeUtils&MockObject $dateTimeUtilsMock;
    private AvailabilityService $availabilityService;

    protected function setUp(): void
    {
        $this->reservationRepositoryMock = $this->createMock(ReservationRepository::class);
        $this->workingHoursServiceMock = $this->createMock(WorkingHoursService::class);
        $this->dateTimeUtilsMock = $this->createMock(DateTimeUtils::class);

        $this->availabilityService = new AvailabilityService(
            $this->reservationRepositoryMock,
            $this->workingHoursServiceMock,
            $this->dateTimeUtilsMock
        );
    }

    #[DataProviderExternal(AvailabilityServiceDataProvider::class, 'getScheduleAvailabilityDataCases')]
    public function testGetScheduleAvailabilityReturnsExpectedAvailability(
        array $weeklyWorkingHours,
        array $customWorkingHours,
        array $reservationsTimeWindows,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        int $scheduleDivision,
        int $availabilityOffset,
        array $expectedWorkingHours,
        array $availableTimeWindowsResult,
        array $serviceFilterResults,
        array $expectedResult
    ): void
    {

        $reservationsMock = array_map(function($timeWindow){
            $reservationMock = $this->createMock(Reservation::class);
            $reservationMock->method('getStartDateTime')->willReturn($timeWindow->getStartTime());
            $reservationMock->method('getEndDateTime')->willReturn($timeWindow->getEndTime());
            return $reservationMock;
        }, $reservationsTimeWindows);

        $this->reservationRepositoryMock->method('getScheduleReservations')->willReturn($reservationsMock);
        $this->workingHoursServiceMock->method('getScheduleWeeklyWorkingHours')->willReturn($weeklyWorkingHours);
        $this->workingHoursServiceMock->method('getScheduleCustomWorkingHours')->willReturn($customWorkingHours);

        $timeWindowsMergedMock = array_fill(0, 1, new TimeWindow(new DateTime, new DateTime));

        $arg1CallNr = 1;
        $arg2CallNr = 1;
        $this->dateTimeUtilsMock
            ->method('timeWindowCollectionDiff')
            ->with(
                $this->callback(function($collection2) use (&$arg1CallNr, $expectedWorkingHours, $timeWindowsMergedMock){
                    $valid = match($arg1CallNr){
                        1 => $collection2 == $expectedWorkingHours,
                        2 => $collection2 === $timeWindowsMergedMock
                    };
                    $arg1CallNr++;
                    return $valid;
                }),
                $this->callback(function($collection2) use (&$arg2CallNr, $endDate, $reservationsTimeWindows){
                    $valid = match($arg2CallNr){
                        1 => $collection2[0]->getStartTime() == $endDate && $collection2[0]->getEndTime() == $endDate->modify('+1 day'),
                        2 => $collection2 == $reservationsTimeWindows
                    };
                    $arg2CallNr++;
                    return $valid;
                }),
            )
            ->willReturnOnConsecutiveCalls($expectedWorkingHours, $availableTimeWindowsResult);

        $this->dateTimeUtilsMock
            ->method('mergeAdjacentTimeWindows')
            ->with($expectedWorkingHours)
            ->willReturn($timeWindowsMergedMock);

        $scheduleMock = $this->createMock(Schedule::class);
        $scheduleMock->method('getDivision')->willReturn($scheduleDivision);
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
            $endDate
        );

        $this->assertEquals($expectedResult, $result);
    }
}
