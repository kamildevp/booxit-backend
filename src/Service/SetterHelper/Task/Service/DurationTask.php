<?php

namespace App\Service\SetterHelper\Task\Service;

use App\Entity\Service;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use DateInterval;
use Exception;

/** @property Service $object */
class DurationTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function runPreValidation(string $duration)
    {
        try{
            $interval = new DateInterval($duration);
        }
        catch(Exception){
             $this->validationErrors['duration'] = "Invalid duration format";
             return;
        }

        $this->object->setDuration($interval);
    }




}