<?php

namespace App\Scheduler;

use App\Scheduler\Message\DbCleanupMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule]
class DbCleanupTaskProvider implements ScheduleProviderInterface
{
    private Schedule $schedule;

    public function getSchedule(): Schedule
    {
        return $this->schedule ??= (new Schedule())
            ->with(
                RecurringMessage::cron('* * * * *', new DbCleanupMessage())
            );
    }
}