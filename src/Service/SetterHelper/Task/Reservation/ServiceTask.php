<?php

namespace App\Service\SetterHelper\Task\Reservation;

use App\Entity\Reservation;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;

/** @property Reservation $object */
class ServiceTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function runPreValidation(int $serviceId):void
    {
        $schedule = $this->object->getSchedule();
        if(!$schedule){
            return;
        }

        $services = $schedule->getServices();
        $service = $services->findFirst(function($key, $service) use ($serviceId){
            return $service->getId() === $serviceId;
        });

        if(!$service){
            $this->requestErrors['serviceId'] = "Service with id = {$serviceId} does not exist";
            return;
        }
        
        $this->object->setService($service);
        $this->object->updateTimeWindow();  
    }





}