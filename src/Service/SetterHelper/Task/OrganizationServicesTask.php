<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\Organization;
use App\Entity\Service;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\SetterHelperInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @property Organization $object */
class OrganizationServicesTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    const MODIFICATION_TYPES = ['ADD', 'REMOVE', 'PATCH', 'OVERWRITE'];

    public function __construct(private SetterHelperInterface $setterHelper, private ValidatorInterface $validator)
    {
        
    }

    public function runPreValidation(array $services, string $modificationType)
    {
        if(!in_array($modificationType, self::MODIFICATION_TYPES)){
            throw new InvalidRequestException('Invalid modification type. Allowed modifications types: ADD, PATCH, REMOVE, OVERWRITE');
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
                throw new InvalidRequestException("Parameter services parameter must be array of integers");
            }

            $service = $this->object->getServices()->findFirst(function($key, $element) use ($serviceId){
                return $element->getId() === $serviceId;
            });
            
            if(!$service){
                throw new InvalidRequestException("Service with id = {$serviceId} does not exist");
            }
            
            $this->object->removeService($service);
        }
    }

    private function addServices(array $services):void
    {
        $loop_indx = 0;
        foreach($services as $settings){
            $newService = new Service();
            if(!is_array($settings)){
                throw new InvalidRequestException("Parameter services parameter must be array of settings arrays");
            }

            $this->object->addService($newService);

            $this->setterHelper->updateObjectSettings($newService, $settings, ['Default']);
            $this->validateService($loop_indx, $newService, $this->setterHelper->getValidationErrors());
            $loop_indx++;
        }
    }

    private function patchServices(array $services):void
    {
        $loop_indx = 0;
        foreach($services as $settings){
            if(!is_array($settings)){
                throw new InvalidRequestException("Parameter services parameter must be array of settings arrays");
            }

            if(!array_key_exists('id', $settings)){
                throw new InvalidRequestException('Parameter id is required');
            }

            $id = $settings['id'];
            $service = $this->object->getServices()->findFirst(function($key,$element) use ($id){
                return $element->getId() == $id;
            });
            unset($settings['id']);

            if(!$service){
                throw new InvalidRequestException("Service with id = {$id} does not exist");
            }

            $this->setterHelper->updateObjectSettings($service, $settings);
            $this->validateService($loop_indx, $service, $this->setterHelper->getValidationErrors());
            $loop_indx++;
        }
    }

    private function overwriteServices(array $services):void
    {
        $organizationServices = new ArrayCollection($this->object->getServices()->getValues());
        $this->object->getServices()->clear();

        $loop_indx = 0;
        foreach($services as $settings){
            if(array_key_exists('id', $settings)){
                $id = $settings['id'];
                $service = $organizationServices->findFirst(function($key,$element) use ($id){
                    return $element->getId() == $id;
                });
                if(!$service){
                    throw new InvalidRequestException("Service with id = {$id} does not exist");
                }
                unset($settings['id']);
            }
            else{
                $service = new Service();
            }

            $this->object->addService($service);
            $this->setterHelper->updateObjectSettings($service, $settings, ['Default']);
            $this->validateService($loop_indx, $service, $this->setterHelper->getValidationErrors());
            $loop_indx++;
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