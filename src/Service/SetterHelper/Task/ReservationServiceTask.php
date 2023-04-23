<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\Reservation;
use App\Service\SetterHelper\Trait\SetterTaskTrait;

/** @property Reservation $object */
class ReservationServiceTask implements SetterTaskInterface
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
            $this->validationErrors['service'] = "Service not found";
            return;
        }
        
        $this->object->setService($service);
        $this->object->updateTimeWindow();  
    }





}