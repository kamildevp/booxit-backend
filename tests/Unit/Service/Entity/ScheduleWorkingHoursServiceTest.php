<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity;

use App\DTO\WorkingHours\TimeWindowDTO;
use App\Service\Entity\ScheduleWorkingHoursService;
use App\Repository\ScheduleRepository;
use App\Entity\Schedule;
use App\Entity\WeekdayTimeWindow;
use App\DTO\WorkingHours\WeeklyWorkingHoursDTO;
use App\Enum\Weekday;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;

class ScheduleWorkingHoursServiceTest extends TestCase
{
    private ScheduleRepository&MockObject $scheduleRepositoryMock;
    private ScheduleWorkingHoursService $service;

    protected function setUp(): void
    {
        $this->scheduleRepositoryMock = $this->createMock(ScheduleRepository::class);
        $this->service = new ScheduleWorkingHoursService($this->scheduleRepositoryMock);
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
}
