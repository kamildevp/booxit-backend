<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\Schedule;
use App\Entity\TimeWindow;
use App\Entity\WorkingHours;
use App\Exceptions\InvalidRequestException;
use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\Common\Collections\Collection;

/** @property Schedule $object */
class WorkingHoursTask implements SetterTaskInterface
{
    use SetterTaskTrait;
    const WEEK_DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public function __construct(private SetterHelperInterface $setterHelper)
    {

    }

    public function runPreValidation(array $workingHours)
    {
        foreach($workingHours as $day => $timeWindows){
            if(!in_array($day, self::WEEK_DAYS) && !(new DataHandlingHelper)->validateDateTime($day, 'Y-m-d')){
                throw new InvalidRequestException("Parameter {$day} is not valid date or day of week");
            }
            
            $workHours = $this->object->getDayWorkingHours($day);
            if($timeWindows === null){
                $this->object->removeWorkingHours($workHours);
                continue;
            }

            if(!($workHours instanceof WorkingHours)){
                $workHours = new WorkingHours();
                $workHours->setDay($day);
                $this->object->addWorkingHours($workHours);
            }

            
            $definedTimeWindows = $workHours->getTimeWindows();
            $timeWindowsDiff = count($definedTimeWindows) - count($timeWindows);

            for($i=0; $i<$timeWindowsDiff; $i++){
                $definedTimeWindows->removeElement($definedTimeWindows->last());
            }

            $loop_indx = 0;
            foreach($timeWindows as $timeWindowSettings){
                $timeWindow = $definedTimeWindows[$loop_indx] ?? new TimeWindow();
                $this->setterHelper->updateObjectSettings($timeWindow, $timeWindowSettings);
                $workHours->addTimeWindow($timeWindow);
                $loop_indx++;
            }
            $this->validateTimeWindows($workHours->getTimeWindows());
        }
    }

    private function validateTimeWindows(Collection $timeWindows){
        
        foreach($timeWindows as $timeWindow){
            $timeWindowsOverlay = $timeWindows->exists(function($key, $element) use ($timeWindow){
                $timeWindowStart = $timeWindow->getStartTime();
                $timeWindowEnd = $timeWindow->getEndTime();
                $elementStart = $element->getStartTime();
                $elementEnd = $element->getEndTime();

                return $element != $timeWindow  && (
                    ($elementStart <= $timeWindowStart && $timeWindowStart < $elementEnd) || 
                    ($elementEnd < $timeWindowEnd  && $timeWindowEnd <= $elementEnd)
                );
            });

            if($timeWindowsOverlay){
                $day = $timeWindow->getWorkingHours()->getDay();
                throw new InvalidRequestException("Time windows defined for {$day} are overlaping");
            }
        }
    }

}