<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\Schedule;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\ORM\EntityManagerInterface;
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
            $this->validationErrors['schedule'] = "Schedule not found";
            return;
        }

        $this->object->setSchedule($schedule);
    }

}