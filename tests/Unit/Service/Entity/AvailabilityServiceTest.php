<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity;

use App\DTO\WorkingHours\ScheduleAvailabilityGetDTO;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Entity\Reservation;
use App\Enum\Weekday;
use App\Model\TimeWindow;
use App\Repository\ReservationRepository;
use App\Repository\ServiceRepository;
use App\Service\Entity\AvailabilityService;
use App\Service\Entity\WorkingHoursService;
use App\Service\Utils\DateTimeUtils;
use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AvailabilityServiceTest extends TestCase
{
    private ReservationRepository&MockObject $reservationRepositoryMock;
    private ServiceRepository&MockObject $serviceRepositoryMock;
    private WorkingHoursService&MockObject $workingHoursServiceMock;
    private DateTimeUtils&MockObject $dateTimeUtilsMock;
    private AvailabilityService $availabilityService;

    protected function setUp(): void
    {
        $this->reservationRepositoryMock = $this->createMock(ReservationRepository::class);
        $this->serviceRepositoryMock = $this->createMock(ServiceRepository::class);
        $this->workingHoursServiceMock = $this->createMock(WorkingHoursService::class);
        $this->dateTimeUtilsMock = $this->createMock(DateTimeUtils::class);

        $this->availabilityService = new AvailabilityService(
            $this->reservationRepositoryMock,
            $this->serviceRepositoryMock,
            $this->workingHoursServiceMock,
            $this->dateTimeUtilsMock
        );
    }

    public function testGetScheduleAvailabilityReturnsExpectedAvailability(): void
    {
        $scheduleMock = $this->createMock(Schedule::class);
        $dto = new ScheduleAvailabilityGetDTO('2025-10-06', '2025-10-07');

        $this->dateTimeUtilsMock->method('resolveDateTimeImmutableWithDefault')->willReturnCallback(fn($value) => new DateTimeImmutable($value));

        $weekdayTimeWindows = [new TimeWindow(new DateTimeImmutable('09:00'), new DateTimeImmutable('17:00'))];
        $weeklyWorkingHours = [
            Weekday::MONDAY->value => $weekdayTimeWindows,
            Weekday::TUESDAY->value => $weekdayTimeWindows,
        ];

        $customTimeWindows = [new TimeWindow(new DateTimeImmutable('09:00'), new DateTimeImmutable('15:00'))];
        $customWorkingHours = [
            '2025-10-07' => $customTimeWindows
        ];

        $reservationMock1 = $this->createMock(Reservation::class);
        $reservation1TimeWindow = new TimeWindow(new DateTimeImmutable('2025-10-06 9:00'), new DateTimeImmutable('2025-10-06 11:00'));
        $reservationMock1->method('getStartDateTime')->willReturn($reservation1TimeWindow->getStartTime());
        $reservationMock1->method('getEndDateTime')->willReturn($reservation1TimeWindow->getEndTime());
        $reservationMock2 = $this->createMock(Reservation::class);

        $reservation2TimeWindow = new TimeWindow(new DateTimeImmutable('2025-10-07 14:00'), new DateTimeImmutable('2025-10-07 15:00'));
        $reservationMock2->method('getStartDateTime')->willReturn($reservation2TimeWindow->getStartTime());
        $reservationMock2->method('getEndDateTime')->willReturn($reservation2TimeWindow->getEndTime());

        $this->reservationRepositoryMock->method('getScheduleReservations')->willReturn([$reservationMock1, $reservationMock2]);
        $this->workingHoursServiceMock->method('getScheduleWeeklyWorkingHours')->willReturn($weeklyWorkingHours);
        $this->workingHoursServiceMock->method('getScheduleCustomWorkingHours')->willReturn($customWorkingHours);

        $timeWindowDiff1 = [new TimeWindow(new DateTimeImmutable('11:00'), new DateTimeImmutable('17:00'))];
        $timeWindowDiff2 = [new TimeWindow(new DateTimeImmutable('09:00'), new DateTimeImmutable('14:00'))];
        $this->dateTimeUtilsMock
            ->method('timeWindowCollectionDiff')
            ->willReturnOnConsecutiveCalls($timeWindowDiff1, $timeWindowDiff2);

        $this->dateTimeUtilsMock->expects($this->never())->method('compareDateIntervals');

        $result = $this->availabilityService->getScheduleAvailability($scheduleMock, $dto);

        $this->assertArrayHasKey('2025-10-06', $result);
        $this->assertEquals($result['2025-10-06'], $timeWindowDiff1);
        $this->assertArrayHasKey('2025-10-07', $result);
        $this->assertEquals($result['2025-10-07'], $timeWindowDiff2);
    }


    public function testGetScheduleAvailabilityWithServiceFilterReturnsExpectedAvailability(): void
    {
        $scheduleMock = $this->createMock(Schedule::class);
        $dto = new ScheduleAvailabilityGetDTO('2025-10-06', '2025-10-07', 10);

        $serviceMock = $this->createMock(Service::class);
        $serviceMock->method('getDuration')->willReturn(new DateInterval('PT30M'));

        $this->serviceRepositoryMock->method('find')->with(10)->willReturn($serviceMock);

        $this->dateTimeUtilsMock->method('resolveDateTimeImmutableWithDefault')->willReturnCallback(fn($value) => new DateTimeImmutable($value));

        $weekdayTimeWindows = [new TimeWindow(new DateTimeImmutable('09:00'), new DateTimeImmutable('17:00'))];
        $weeklyWorkingHours = [
            Weekday::MONDAY->value => $weekdayTimeWindows,
            Weekday::TUESDAY->value => $weekdayTimeWindows,
        ];

        $customTimeWindows = [new TimeWindow(new DateTimeImmutable('09:00'), new DateTimeImmutable('15:00'))];
        $customWorkingHours = [
            '2025-10-07' => $customTimeWindows
        ];

        $reservationMock1 = $this->createMock(Reservation::class);
        $reservation1TimeWindow = new TimeWindow(new DateTimeImmutable('2025-10-06 9:00'), new DateTimeImmutable('2025-10-06 11:00'));
        $reservationMock1->method('getStartDateTime')->willReturn($reservation1TimeWindow->getStartTime());
        $reservationMock1->method('getEndDateTime')->willReturn($reservation1TimeWindow->getEndTime());
        $reservationMock2 = $this->createMock(Reservation::class);

        $reservation2TimeWindow = new TimeWindow(new DateTimeImmutable('2025-10-07 14:00'), new DateTimeImmutable('2025-10-07 15:00'));
        $reservationMock2->method('getStartDateTime')->willReturn($reservation2TimeWindow->getStartTime());
        $reservationMock2->method('getEndDateTime')->willReturn($reservation2TimeWindow->getEndTime());

        $this->reservationRepositoryMock->method('getScheduleReservations')->willReturn([$reservationMock1, $reservationMock2]);
        $this->workingHoursServiceMock->method('getScheduleWeeklyWorkingHours')->willReturn($weeklyWorkingHours);
        $this->workingHoursServiceMock->method('getScheduleCustomWorkingHours')->willReturn($customWorkingHours);

        $timeWindowDiff1 = [new TimeWindow(new DateTimeImmutable('11:00'), new DateTimeImmutable('17:00'))];
        $timeWindowDiff2 = [new TimeWindow(new DateTimeImmutable('09:00'), new DateTimeImmutable('14:00'))];
        $this->dateTimeUtilsMock
            ->method('timeWindowCollectionDiff')
            ->willReturnOnConsecutiveCalls($timeWindowDiff1, $timeWindowDiff2);

        $this->dateTimeUtilsMock->method('compareDateIntervals')->willReturn(1);

        $result = $this->availabilityService->getScheduleAvailability($scheduleMock, $dto);

        $this->assertArrayHasKey('2025-10-06', $result);
        $this->assertEquals($result['2025-10-06'], $timeWindowDiff1);
        $this->assertArrayHasKey('2025-10-07', $result);
        $this->assertEquals($result['2025-10-07'], $timeWindowDiff2);
    }

    public function testGetScheduleAvailabilityWithServiceFilterLimitingResultsReturnsExpectedAvailability(): void
    {
        $scheduleMock = $this->createMock(Schedule::class);
        $dto = new ScheduleAvailabilityGetDTO('2025-10-06', '2025-10-07', 10);

        $serviceMock = $this->createMock(Service::class);
        $serviceMock->method('getDuration')->willReturn(new DateInterval('PT30M'));

        $this->serviceRepositoryMock->method('find')->with(10)->willReturn($serviceMock);

        $this->dateTimeUtilsMock->method('resolveDateTimeImmutableWithDefault')->willReturnCallback(fn($value) => new DateTimeImmutable($value));

        $weekdayTimeWindows = [new TimeWindow(new DateTimeImmutable('09:00'), new DateTimeImmutable('17:00'))];
        $weeklyWorkingHours = [
            Weekday::MONDAY->value => $weekdayTimeWindows,
            Weekday::TUESDAY->value => $weekdayTimeWindows,
        ];

        $customTimeWindows = [new TimeWindow(new DateTimeImmutable('09:00'), new DateTimeImmutable('15:00'))];
        $customWorkingHours = [
            '2025-10-07' => $customTimeWindows
        ];

        $reservationMock1 = $this->createMock(Reservation::class);
        $reservation1TimeWindow = new TimeWindow(new DateTimeImmutable('2025-10-06 9:00'), new DateTimeImmutable('2025-10-06 11:00'));
        $reservationMock1->method('getStartDateTime')->willReturn($reservation1TimeWindow->getStartTime());
        $reservationMock1->method('getEndDateTime')->willReturn($reservation1TimeWindow->getEndTime());
        $reservationMock2 = $this->createMock(Reservation::class);

        $reservation2TimeWindow = new TimeWindow(new DateTimeImmutable('2025-10-07 14:00'), new DateTimeImmutable('2025-10-07 15:00'));
        $reservationMock2->method('getStartDateTime')->willReturn($reservation2TimeWindow->getStartTime());
        $reservationMock2->method('getEndDateTime')->willReturn($reservation2TimeWindow->getEndTime());

        $this->reservationRepositoryMock->method('getScheduleReservations')->willReturn([$reservationMock1, $reservationMock2]);
        $this->workingHoursServiceMock->method('getScheduleWeeklyWorkingHours')->willReturn($weeklyWorkingHours);
        $this->workingHoursServiceMock->method('getScheduleCustomWorkingHours')->willReturn($customWorkingHours);

        $timeWindowDiff1 = [new TimeWindow(new DateTimeImmutable('11:00'), new DateTimeImmutable('17:00'))];
        $timeWindowDiff2 = [new TimeWindow(new DateTimeImmutable('09:00'), new DateTimeImmutable('14:00'))];
        $this->dateTimeUtilsMock
            ->method('timeWindowCollectionDiff')
            ->willReturnOnConsecutiveCalls($timeWindowDiff1, $timeWindowDiff2);

        $this->dateTimeUtilsMock->method('compareDateIntervals')->willReturnOnConsecutiveCalls(1,-1);

        $result = $this->availabilityService->getScheduleAvailability($scheduleMock, $dto);

        $this->assertArrayHasKey('2025-10-06', $result);
        $this->assertEquals($result['2025-10-06'], $timeWindowDiff1);
        $this->assertArrayHasKey('2025-10-07', $result);
        $this->assertEquals($result['2025-10-07'], []);
    }
}
