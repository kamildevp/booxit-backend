<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\TimeWindow;
use App\Entity\WorkingHours;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/** @property WorkingHours $object */
class WorkingHoursTimeWindowsTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function __construct(private SetterHelperInterface $setterHelper)
    {

    }

    public function runPreValidation(array $timeWindows)
    {
        $timeWindowsCollection = new ArrayCollection($this->object->getTimeWindows()->getValues());
        $this->object->getTimeWindows()->clear();

        foreach($timeWindows as $settings){
            if(!is_array($settings)){
                throw new InvalidRequestException("Parameter time_windows parameter must be array of settings arrays");
            }

            $timeWindow = $timeWindowsCollection->current() ?  $timeWindowsCollection->current() : new TimeWindow();

            $this->setterHelper->updateObjectSettings($timeWindow, $settings, ['Default']);
            $this->object->addTimeWindow($timeWindow);

            $timeWindowsCollection->next();
        }

        $this->validateTimeWindows($this->object->getTimeWindows());
    }

    /** @param Collection<int, TimeWindow>  $timeWindows*/
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
                $day = $this->object->getDay();
                throw new InvalidRequestException("Time windows defined for {$day} are overlaping");
            }
        }
    }

    

    
}