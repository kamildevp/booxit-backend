<?php

namespace App\Service\SetterHelper\Task\Schedule;

use App\Entity\Schedule;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;


/** @property Schedule $object */
class ServicesTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    const MODIFICATION_TYPES = ['ADD', 'REMOVE', 'OVERWRITE'];

    public function __construct(private SetterHelperInterface $setterHelper)
    {
        
    }

    public function runPreValidation(array $services, string $modificationType)
    {
        if(!in_array($modificationType, self::MODIFICATION_TYPES)){
            $modificationTypesString = join(', ', self::MODIFICATION_TYPES);
            $this->validationErrors['modificationType'] = "Invalid modification type. Allowed modifications types: {$modificationTypesString}";
            return;
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
                $this->requestErrors['services'] = "Parameter must be array of integers";
                return;
            }

            $service = $this->object->getServices()->findFirst(function($key, $element) use ($serviceId){
                return $element->getId() === $serviceId;
            });
            
            if(!$service){
                $this->requestErrors['services'][] = "Service with id = {$serviceId} is not assigned to schedule";
                continue;
            }
            
            $this->object->removeService($service);
        }
    }

    private function addServices(array $services):void
    {
        foreach($services as $serviceId){
            if(!is_int($serviceId)){
                $this->requestErrors['services'] = "Parameter must be array of integers";
                return;
            }

            $serviceAssigned = $this->object->getServices()->exists(function($key, $element) use ($serviceId){
                return $element->getId() === $serviceId;
            });
            
            if($serviceAssigned){
                $this->requestErrors['services'][] = "Service with id = {$serviceId} is already assigned to schedule";
                continue;
            }

            $service = $this->object->getOrganization()->getServices()->findFirst(function($key, $element) use ($serviceId){
                return $element->getId() === $serviceId;
            });

            if(!$service){
                $this->requestErrors['services'][] = "Service with id = {$serviceId} does not exist";
                continue;
            }
            
            $this->object->addService($service);
        }
    }

    private function overwriteServices(array $services):void
    {
        $this->object->getServices()->clear();

        foreach($services as $serviceId){
            if(!is_int($serviceId)){
                $this->requestErrors['services'] = "Parameter must be array of integers";
                return;
            }

            $service = $this->object->getOrganization()->getServices()->findFirst(function($key, $element) use ($serviceId){
                return $element->getId() === $serviceId;
            });

            if(!$service){
                $this->requestErrors['services'][] = "Service with id = {$serviceId} does not exist";
                continue;
            }
            
            $this->object->addService($service);
        }
    }

}