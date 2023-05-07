<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\Schedule;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;


/** @property Schedule $object */
class ScheduleServicesTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    const MODIFICATION_TYPES = ['ADD', 'REMOVE', 'OVERWRITE'];

    public function __construct(private SetterHelperInterface $setterHelper)
    {
        
    }

    public function runPreValidation(array $services, string $modificationType)
    {
        if(!in_array($modificationType, self::MODIFICATION_TYPES)){
            throw new InvalidRequestException('Invalid modification type. Allowed modifications types: ADD, REMOVE, OVERWRITE');
        }

        switch($modificationType){
            case 'REMOVE':
                $this->removeServices($services);
                break;
            case 'ADD':
                $this->addServices($services);
                break;
            case 'OVERWRITE':
                $this->overwriteServices($services);
                break;
        }
        
    }

    private function removeServices(array $services):void
    {
        foreach($services as $serviceId){
            if(!is_int($serviceId)){
                throw new InvalidRequestException("Parameter services parameter must be array of integers");
            }

            $service = $this->object->getServices()->findFirst(function($key, $element) use ($serviceId){
                return $element->getId() === $serviceId;
            });
            
            if(!$service){
                throw new InvalidRequestException("Service with id = {$serviceId} is not assigned to schedule");
            }
            
            $this->object->removeService($service);
        }
    }

    private function addServices(array $services):void
    {
        foreach($services as $serviceId){
            if(!is_int($serviceId)){
                throw new InvalidRequestException("Parameter services parameter must be array of integers");
            }

            $serviceExist = $this->object->getServices()->exists(function($key, $element) use ($serviceId){
                return $element->getId() === $serviceId;
            });
            
            if($serviceExist){
                throw new InvalidRequestException("Service with id = {$serviceId} is already assigned to schedule");
            }

            $service = $this->object->getOrganization()->getServices()->findFirst(function($key, $element) use ($serviceId){
                return $element->getId() === $serviceId;
            });

            if(!$service){
                throw new InvalidRequestException("Service with id = {$serviceId} does not exist");
            }
            
            $this->object->addService($service);
        }
    }

    private function overwriteServices(array $services):void
    {
        $this->object->getServices()->clear();

        foreach($services as $serviceId){
            if(!is_int($serviceId)){
                throw new InvalidRequestException("Parameter services parameter must be array of integers");
            }

            $service = $this->object->getOrganization()->getServices()->findFirst(function($key, $element) use ($serviceId){
                return $element->getId() === $serviceId;
            });

            if(!$service){
                throw new InvalidRequestException("Service with id = {$serviceId} does not exist");
            }
            
            $this->object->addService($service);
        }
    }

}