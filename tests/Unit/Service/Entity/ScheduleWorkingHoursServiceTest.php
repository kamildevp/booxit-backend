<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity;

use App\DTO\WorkingHours\DateWorkingHoursDTO;
use App\DTO\WorkingHours\TimeWindowDTO;
use App\Service\Entity\ScheduleWorkingHoursService;
use App\Repository\ScheduleRepository;
use App\Entity\Schedule;
use App\Entity\WeekdayTimeWindow;
use App\DTO\WorkingHours\WeeklyWorkingHoursDTO;
use App\Entity\DateTimeWindow;
use App\Enum\Weekday;
use App\Repository\DateTimeWindowRepository;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;

class ScheduleWorkingHoursServiceTest extends TestCase
{
    private ScheduleRepository&MockObject $scheduleRepositoryMock;
    private DateTimeWindowRepository&MockObject $dateTimeWindowRepositoryMock;
    private ScheduleWorkingHoursService $service;

    protected function setUp(): void
    {
        $this->scheduleRepositoryMock = $this->createMock(ScheduleRepository::class);
        $this->dateTimeWindowRepositoryMock = $this->createMock(DateTimeWindowRepository::class);
        $this->service = new ScheduleWorkingHoursService($this->scheduleRepositoryMock, $this->dateTimeWindowRepositoryMock);
    }

    public function testSetScheduleWeeklyWorkingHoursCreatesAndRemovesTimeWindows(): void
    {
        $existingTimeWindowMock = $this->createMock(WeekdayTimeWindow::class);
        $existingTimeWindowMock->method('getWeekDay')->willReturn(Weekday::MONDAY->value);
        $existingTimeWindowMock->method('getStartTime')->willReturn(DateTimeImmutable::createFromFormat('H:i','08:00'));
        $existingTimeWindowMock->method('getEndTime')->willReturn(DateTimeImmutable::createFromFormat('H:i','12:00'));
        $scheduleWeekdayTimeWindows = new ArrayCollection([$existingTimeWindowMock]);
        $scheduleMock = $this->createMock(Schedule::class);

        $scheduleMock->method('getWeekdayTimeWindows')->willReturn($scheduleWeekdayTimeWindows);

        $scheduleMock
            ->expects($this->once())
            ->method('addWeekdayTimeWindow')
            ->with($this->callback(function ($timeWindow) {
                return 
                    $timeWindow instanceof WeekdayTimeWindow && 
                    $timeWindow->getWeekday() == Weekday::MONDAY->value &&
                    $timeWindow->getStartTime()->format('H:i')  == '13:00' &&
                    $timeWindow->getEndTime()->format('H:i')  == '17:00';
            }));

        $scheduleMock
            ->expects($this->once())
            ->method('removeWeekdayTimeWindow')
            ->with($existingTimeWindowMock);

        $this->scheduleRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($scheduleMock, true);

        $dto = new WeeklyWorkingHoursDTO([new TimeWindowDTO('13:00', '17:00')], [], [], [], [], [], []);
        $this->service->setScheduleWeeklyWorkingHours($scheduleMock, $dto);
    }

    public function testSetScheduleDateWorkingHoursCreatesAndRemovesTimeWindows(): void
    {
        $date = '2025-10-01';
        $datetime = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        $existingTimeWindowMock = $this->createMock(DateTimeWindow::class);
        $existingTimeWindowMock->method('getDate')->willReturn($datetime);
        $existingTimeWindowMock->method('getStartTime')->willReturn(DateTimeImmutable::createFromFormat('H:i','08:00'));
        $existingTimeWindowMock->method('getEndTime')->willReturn(DateTimeImmutable::createFromFormat('H:i','12:00'));
        $scheduleMock = $this->createMock(Schedule::class);

        $this->dateTimeWindowRepositoryMock
            ->method('findBy')
            ->with(['schedule' => $scheduleMock, 'date' => $datetime])
            ->willReturn([$existingTimeWindowMock]);

        $this->dateTimeWindowRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($timeWindow) use ($datetime, $scheduleMock) {
                return 
                    $timeWindow instanceof DateTimeWindow && 
                    $timeWindow->getSchedule() == $scheduleMock &&
                    $timeWindow->getDate() == $datetime &&
                    $timeWindow->getStartTime()->format('H:i')  == '13:00' &&
                    $timeWindow->getEndTime()->format('H:i')  == '17:00';
            }));

        $this->dateTimeWindowRepositoryMock
            ->expects($this->once())
            ->method('remove')
            ->with($this->callback(function ($timeWindow) use ($datetime) {
                return 
                    $timeWindow instanceof DateTimeWindow && 
                    $timeWindow->getDate() == $datetime &&
                    $timeWindow->getStartTime()->format('H:i')  == '08:00' &&
                    $timeWindow->getEndTime()->format('H:i')  == '12:00';
            }));

        $this->dateTimeWindowRepositoryMock
            ->expects($this->once())
            ->method('flush');

        $dto = new DateWorkingHoursDTO($date, [new TimeWindowDTO('13:00', '17:00')]);
        $this->service->setScheduleDateWorkingHours($scheduleMock, $dto);
    }
}
