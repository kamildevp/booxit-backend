<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity;

use App\DTO\WorkingHours\CustomWorkingHoursUpdateDTO;
use App\DTO\WorkingHours\TimeWindowDTO;
use App\Service\Entity\WorkingHoursService;
use App\Repository\ScheduleRepository;
use App\Entity\Schedule;
use App\Entity\WeekdayTimeWindow;
use App\DTO\WorkingHours\WeeklyWorkingHoursUpdateDTO;
use App\Entity\CustomTimeWindow;
use App\Enum\Weekday;
use App\Repository\CustomTimeWindowRepository;
use App\Service\Utils\DateTimeUtils;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;

class WorkingHoursServiceTest extends TestCase
{
    private ScheduleRepository&MockObject $scheduleRepositoryMock;
    private CustomTimeWindowRepository&MockObject $customTimeWindowRepositoryMock;
    private DateTimeUtils&MockObject $dateTimeUtilsMock;
    private WorkingHoursService $service;
    private DateTimeZone $defaultTimezone;

    protected function setUp(): void
    {
        $this->scheduleRepositoryMock = $this->createMock(ScheduleRepository::class);
        $this->customTimeWindowRepositoryMock = $this->createMock(CustomTimeWindowRepository::class);
        $this->dateTimeUtilsMock = $this->createMock(DateTimeUtils::class);
        $this->defaultTimezone = new DateTimeZone('UTC');

        $this->service = new WorkingHoursService(
            $this->scheduleRepositoryMock,
            $this->customTimeWindowRepositoryMock,
            $this->dateTimeUtilsMock,
            $this->defaultTimezone->getName(),
        );
    }

    public function testSetScheduleWeeklyWorkingHoursCreatesAndRemovesTimeWindows(): void
    {
        $existingTimeWindowMock = $this->createMock(WeekdayTimeWindow::class);
        $existingTimeWindowMock->method('getWeekDay')->willReturn(Weekday::MONDAY->value);
        $existingTimeWindowMock->method('getStartTime')->willReturn(DateTimeImmutable::createFromFormat('H:i','08:00'));
        $existingTimeWindowMock->method('getEndTime')->willReturn(DateTimeImmutable::createFromFormat('H:i','12:00'));
        $existingTimeWindowMock->method('getTimezone')->willReturn($this->defaultTimezone->getName());
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
                    $timeWindow->getTimezone() == $this->timezone;
            }));

        $scheduleMock
            ->expects($this->once())
            ->method('removeWeekdayTimeWindow')
            ->with($existingTimeWindowMock);

        $this->scheduleRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($scheduleMock, true);

        $dto = new WeeklyWorkingHoursUpdateDTO([new TimeWindowDTO('13:00', '17:00')], [], [], [], [], [], [], $this->defaultTimezone->getName());
        $this->service->setScheduleWeeklyWorkingHours($scheduleMock, $dto);
    }

    public function testSetScheduleCustomWorkingHoursCreatesAndRemovesTimeWindows(): void
    {
        $timezone = new DateTimeZone('Asia/Tokyo');
        $dto = new CustomWorkingHoursUpdateDTO('2025-12-01', [new TimeWindowDTO('13:00', '17:00')], $timezone->getName());
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dto->date, $timezone);
        $existingTimeWindowMock = $this->createMock(CustomTimeWindow::class);
        $existingTimeWindowMock->method('getStartDateTime')->willReturn($date->setTime(8,0)->setTimezone($this->defaultTimezone));
        $existingTimeWindowMock->method('getEndDateTime')->willReturn($date->setTime(12,0)->setTimezone($this->defaultTimezone));
        $scheduleMock = $this->createMock(Schedule::class);
        $searchStart = $date->setTime(0,0)->setTimezone($this->defaultTimezone);
        $searchEnd = $date->setTime(23,59)->setTimezone($this->defaultTimezone);

        $this->customTimeWindowRepositoryMock
            ->method('getScheduleCustomTimeWindows')
            ->with(
                $scheduleMock, 
                $this->callback(fn($datetime) => $datetime == $searchStart && $datetime->getTimezone() == $this->defaultTimezone),
                $this->callback(fn($datetime) => $datetime == $searchEnd && $datetime->getTimezone() == $this->defaultTimezone),
            )
            ->willReturn([$existingTimeWindowMock]);

        $this->customTimeWindowRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn($timeWindow) =>
                    $timeWindow instanceof CustomTimeWindow && 
                    $timeWindow->getSchedule() == $scheduleMock &&
                    $timeWindow->getStartDateTime()->format('Y-m-d H:i')  == '2025-12-01 04:00' &&
                    $timeWindow->getEndDateTime()->format('Y-m-d H:i')  == '2025-12-01 08:00'
            ));
        
        $this->customTimeWindowRepositoryMock
            ->expects($this->once())
            ->method('remove')
            ->with($existingTimeWindowMock);

        $this->customTimeWindowRepositoryMock
            ->expects($this->once())
            ->method('flush');

        
        $this->service->setScheduleCustomWorkingHours($scheduleMock, $dto);
    }

    public function testGetScheduleCustomWorkingHours(): void
    {
        $customTimeWindowsData = [
            ['date' => '2025-10-13', 'startTime' => '10:00', 'endTime' => '15:00'],
            ['date' => '2025-10-13', 'startTime' => '15:30', 'endTime' => '16:00'],
            ['date' => '2025-10-14', 'startTime' => '08:00', 'endTime' => '12:00']
        ];
        $timezone = new DateTimeZone('Asia/Tokyo');

        $customTimeWindowsMock = array_map(function($element) use ($timezone){
            $mock = $this->createMock(CustomTimeWindow::class);
            $mock->method('getStartDateTime')->willReturn(DateTimeImmutable::createFromFormat('Y-m-d H:i', $element['date'].' '.$element['startTime'], $timezone)->setTimezone($this->defaultTimezone));
            $mock->method('getEndDateTime')->willReturn(DateTimeImmutable::createFromFormat('Y-m-d H:i', $element['date'].' '.$element['endTime'], $timezone)->setTimezone($this->defaultTimezone));
            return $mock;
        }, $customTimeWindowsData);

        $scheduleMock = $this->createMock(Schedule::class);
        $start = DateTimeImmutable::createFromFormat('Y-m-d', '2025-10-13', $timezone);
        $end = DateTimeImmutable::createFromFormat('Y-m-d', '2025-10-14', $timezone);

        $this->dateTimeUtilsMock
            ->method('resolveDateTimeImmutableWithDefault')
            ->willReturnOnConsecutiveCalls($start, $end);

        $this->customTimeWindowRepositoryMock
            ->method('getScheduleCustomTimeWindows')
            ->willReturn($customTimeWindowsMock);

        $result = $this->service->getScheduleCustomWorkingHours($scheduleMock, $start, $end, $timezone);

        foreach ($customTimeWindowsData as $data) {
            $date = $data['date'];
            $this->assertArrayHasKey($date, $result);
            $matchedTimeWindows = array_filter($result[$date],
                fn($tw) => 
                    $tw->getStartTime()->format('H:i') == $data['startTime'] && 
                    $tw->getEndTime()->format('H:i') == $data['endTime']
            );

            $this->assertNotEmpty($matchedTimeWindows);
        }
    }

    public function testGetScheduleWeeklyWorkingHours(): void
    {
        $weekdayTimeWindowsData = [
            ['weekday' => Weekday::MONDAY->value, 'startTime' => '10:00', 'endTime' => '15:00'],
            ['weekday' => Weekday::MONDAY->value, 'startTime' => '15:30', 'endTime' => '16:00'],
            ['weekday' => Weekday::WEDNESDAY->value, 'startTime' => '08:00', 'endTime' => '12:00']
        ];

        $weekdayTimeWindowsMock = array_map(function($element){
            $mock = $this->createMock(WeekdayTimeWindow::class);
            $mock->method('getWeekday')->willReturn($element['weekday']);
            $mock->method('getStartTime')->willReturn(DateTimeImmutable::createFromFormat('H:i', $element['startTime']));
            $mock->method('getEndTime')->willReturn(DateTimeImmutable::createFromFormat('H:i', $element['endTime']));
            $mock->method('getTimezone')->willReturn($this->defaultTimezone->getName());
            return $mock;
        }, $weekdayTimeWindowsData);

        $scheduleMock = $this->createMock(Schedule::class);
        $scheduleMock->method('getWeekdayTimeWindows')->willReturn(new ArrayCollection($weekdayTimeWindowsMock));
        $this->dateTimeUtilsMock->method('sortTimeWindowCollection')->willReturnCallback(fn($value) => $value);

        $result = $this->service->getScheduleWeeklyWorkingHours($scheduleMock);
        
        foreach ($weekdayTimeWindowsData as $data) {
            $weekday = $data['weekday'];
            $this->assertArrayHasKey($weekday, $result);
            $matchedTimeWindows = array_filter($result[$weekday],
                fn($tw) => 
                    $tw->getStartTime()->format('H:i') == $data['startTime'] && 
                    $tw->getEndTime()->format('H:i') == $data['endTime']
            );

            $this->assertNotEmpty($matchedTimeWindows);
        }

        $emptyWeekdayWorkingHours = array_diff(Weekday::values(), array_column($weekdayTimeWindowsData, 'weekday'));
        foreach($emptyWeekdayWorkingHours as $weekday){
            $this->assertArrayHasKey($weekday, $result);
            $this->assertEquals([], $result[$weekday]);
        }

        $this->assertEquals($this->defaultTimezone->getName(), $result['timezone']);
    }
}
