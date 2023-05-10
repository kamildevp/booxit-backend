<?php

namespace App\Service\SetterHelper\Task\Reservation;

use App\Entity\Schedule;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\ORM\EntityManagerInterface;

/** @property Reservation $object */
class ScheduleTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function __construct(private EntityManagerInterface $entityManager)
    {

    }

    public function runPreValidation(int $scheduleId)
    {
        $schedule = $this->entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            $this->requestErrors['scheduleId'] = "Schedule with id = {$scheduleId} does not exist";
            return;
        }

        $this->object->setSchedule($schedule);
    }

}