<?php

namespace App\Service\SetterHelper\Task\Organization;

use App\Entity\Organization;
use App\Entity\Service;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @property Organization $object */
class ServicesTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    const MODIFICATION_TYPES = ['ADD', 'REMOVE', 'PATCH', 'OVERWRITE'];

    public function __construct(private SetterHelperInterface $setterHelper, private ValidatorInterface $validator)
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
            case 'PATCH':
                $this->patchServices($services);
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
                $this->requestErrors['services'][] = "Service with id = {$serviceId} does not exist";
                continue;            
            }
            
            $this->object->removeService($service);
        }
    }

    private function addServices(array $services):void
    {
        $loopIndx = 0;
        foreach($services as $settings){
            $newService = new Service();
            if(!is_array($settings)){
                $this->requestErrors['services'][$loopIndx] = "Parameter must be array of service settings";
                $loopIndx++;
                continue;
            }

            $this->object->addService($newService);

            try{
                $this->setterHelper->updateObjectSettings($newService, $settings, ['Default']);
            }
            catch(InvalidRequestException){
                $this->requestErrors['services'][$loopIndx] = $this->setterHelper->getRequestErrors();
                $loopIndx++;
                continue;
            }

            $this->validateService($loopIndx, $newService, $this->setterHelper->getValidationErrors());
            $loopIndx++;
        }
    }

    private function patchServices(array $services):void
    {
        $loopIndx = 0;
        foreach($services as $settings){
            if(!is_array($settings)){
                $this->requestErrors['services'][$loopIndx] = "Parameter must be array of service settings";
                $loopIndx++;
                continue;
            }

            if(!array_key_exists('id', $settings)){
                $this->requestErrors['services'][$loopIndx]['id'] = 'Parameter is required';
                $loopIndx++;
                continue;
            }

            $id = $settings['id'];
            $service = $this->object->getServices()->findFirst(function($key,$element) use ($id){
                return $element->getId() == $id;
            });
            unset($settings['id']);

            if(!$service){
                $this->requestErrors['services'][$loopIndx]['id'] = "Service with id = {$id} does not exist";
                $loopIndx++;
                continue;
            }

            try{
                $this->setterHelper->updateObjectSettings($service, $settings);
            }
            catch(InvalidRequestException){
                $this->requestErrors['services'][$loopIndx] = $this->getRequestErrors();
                $loopIndx++;
                continue;
            }

            $this->validateService($loopIndx, $service, $this->setterHelper->getValidationErrors());
            $loopIndx++;
        }
    }

    private function overwriteServices(array $services):void
    {
        $organizationServices = new ArrayCollection($this->object->getServices()->getValues());
        $this->object->getServices()->clear();

        $loopIndx = 0;
        foreach($services as $settings){
            if(!is_array($settings)){
                $this->requestErrors['services'][$loopIndx] = "Parameter must be array of service settings";
                $loopIndx++;
                continue;
            }

            if(!array_key_exists('id', $settings)){
                $this->requestErrors['services'][$loopIndx]['id'] = 'Parameter is required';
                $loopIndx++;
                continue;
            }

            $id = $settings['id'];
            if(!is_null($id)){
                $service = $organizationServices->findFirst(function($key,$element) use ($id){
                    return $element->getId() == $id;
                });
                if(!$service){
                    $this->requestErrors['services'][$loopIndx]['id'] = "Service with id = {$id} does not exist";
                    $loopIndx++;
                    continue;
                }
            }
            else{
                $service = new Service();
            }
            
            unset($settings['id']);

            $this->object->addService($service);
            try{
                $this->setterHelper->updateObjectSettings($service, $settings, ['Default']);
            }
            catch(InvalidRequestException){
                $this->requestErrors['services'][$loopIndx] = $this->setterHelper->getRequestErrors();
                $loopIndx++;
                continue;
            }

            $this->validateService($loopIndx, $service, $this->setterHelper->getValidationErrors());
            $loopIndx++;
        }
    }

    private function validateService(string $id, Service $service, array $validationErrors){
        foreach ($validationErrors as $parameterName => $error) {;
            $this->validationErrors['services'][$id][$parameterName] = $error;
        }

        $violations = $this->validator->validate($service);
        
        foreach ($violations as $violation) {
            $parameterAlias = $this->getParameterAlias($violation->getPropertyPath());
            $this->validationErrors['services'][$id][$parameterAlias] = $violation->getMessage();
        }
    }

}