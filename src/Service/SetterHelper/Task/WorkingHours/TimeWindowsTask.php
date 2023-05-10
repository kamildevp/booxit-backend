<?php

namespace App\Service\SetterHelper\Task\WorkingHours;

use App\Entity\TimeWindow;
use App\Entity\WorkingHours;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/** @property WorkingHours $object */
class TimeWindowsTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function __construct(private SetterHelperInterface $setterHelper)
    {

    }

    public function runPreValidation(array $timeWindows)
    {
        $timeWindowsCollection = new ArrayCollection($this->object->getTimeWindows()->getValues());
        $this->object->getTimeWindows()->clear();

        $loopIndx = 0;
        foreach($timeWindows as $settings)
        {
            if(!is_array($settings)){
                $this->validationErrors['timeWindows'] = "Parameter must be array of time window settings arrays";
                return;
            }

            $timeWindow = $timeWindowsCollection->current() ?  $timeWindowsCollection->current() : new TimeWindow();

            try{
                $this->setterHelper->updateObjectSettings($timeWindow, $settings, ['Default']);
            }
            catch(InvalidRequestException){
                $this->requestErrors['timeWindows'][$loopIndx] = $this->setterHelper->getRequestErrors();
                $loopIndx++;
                continue;
            }

            $validationErrors = $this->setterHelper->getValidationErrors();
            if(!empty($validationErrors)){
                $this->validationErrors['timeWindows'][$loopIndx] = $validationErrors;
                $loopIndx++;
                continue;
            }

            $this->object->addTimeWindow($timeWindow);
            $timeWindowsCollection->next();
            $loopIndx++;
        }

        if(!empty($this->requestErrors) || !empty($this->validationErrors)){
            return;
        }

        if(!$this->validateTimeWindows($this->object->getTimeWindows())){
            $this->validationErrors['timeWindows'] = "Time windows are overlaping";
            return;
        }
    }

    /** @param Collection<int, TimeWindow>  $timeWindows*/
    private function validateTimeWindows(Collection $timeWindows):bool
    {
    
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
                return false;
            }
        }
        return true;
    }

    

    
}