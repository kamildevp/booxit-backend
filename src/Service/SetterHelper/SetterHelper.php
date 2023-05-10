<?php

namespace App\Service\SetterHelper;

use App\Exceptions\InvalidRequestException;
use App\Kernel;
use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Service\ObjectHandlingHelper\ObjectHandlingHelper;
use App\Service\SetterHelper\Attribute\Setter;
use App\Service\SetterHelper\Util\RequestParser;
use App\Service\SetterHelper\Util\SetterManager;
use ReflectionClass;

class SetterHelper implements SetterHelperInterface
{
    const SETTER_ATTRIBUTE = Setter::class;

    private array $setterMethods = [];
    private array $settings = [];
    private array $validationGroups = ['Default'];
    private array $validationErrors = [];
    private array $requestErrors = [];

    public function __construct(private Kernel $kernel)
    {

    }

    public function updateObjectSettings(object $object, array $settings, array $requiredGroups = [], array $optionalGroups = ['Default']):void
    {
        $reflectionClass = new ReflectionClass($object);
        
        $this->requestErrors = [];        
        $requestParameters = array_keys($settings);
        $this->setterMethods = [];

        $setterManager = new SetterManager(self::SETTER_ATTRIBUTE, new ObjectHandlingHelper($this->kernel));
        $this->setterMethods = $setterManager->filterSetters($reflectionClass, $requestParameters, $requiredGroups, $optionalGroups);
        $this->requestErrors = array_merge($this->requestErrors, $setterManager->getRequestErrors());

        $requestParser = new RequestParser();
        $requestParser->parseRequestParameters($this->setterMethods, $requestParameters);
        $this->requestErrors = array_merge($this->requestErrors, $requestParser->getRequestErrors());

        if(!empty($this->requestErrors)){
            throw new InvalidRequestException();
        }

        $this->validationGroups = [];
        $this->validationErrors = [];
        foreach($this->setterMethods as $setter){
            $task = $setter->getTask();
            if(is_null($task)){
                $setterName = $setter->getName();
                $value = $settings[$setter->getTargetParameter()];
                $object->{$setterName}($value);
                continue;
            }

            $task->setObject($object);

            $mappedSettings = (new DataHandlingHelper)->replaceArrayKeys($settings, array_flip($setter->getAliases()));
            $task->runPreValidationTask($mappedSettings);

            $this->validationGroups = array_merge($this->validationGroups, $task->getValidationGroups());
            $this->validationErrors = $this->validationErrors + $task->getValidationErrors();
            $this->requestErrors = $this->requestErrors + $task->getRequestErrors();
        }
        $this->settings = $settings;

        if(empty($this->requestErrors) && empty($settings)){
            throw new InvalidRequestException("No parameters found");
        }

        if(!empty($this->requestErrors)){
            throw new InvalidRequestException("Invalid Request");
        }
    }

    
    public function getValidationGroups():array
    {
        return $this->validationGroups;
    }

    public function getValidationErrors():array
    {
        return $this->validationErrors;
    }

    public function runPostValidationTasks():void
    {
        foreach($this->setterMethods as $setter){
            $task = $setter->getTask();
            if(!$task){
                continue;
            }
            $mappedSettings = (new DataHandlingHelper)->replaceArrayKeys($this->settings, array_flip($setter->getAliases()));
            $task->runPostValidationTask($mappedSettings);
        }
    }

    public function getPropertyRequestParameter(string $propertyName):string
    {
        if(!array_key_exists($propertyName, $this->setterMethods)){
            return $propertyName;
        }

        return $this->setterMethods[$propertyName]->getTargetParameter();
    }

    public function getRequestErrors():array
    {
        return $this->requestErrors;
    }

}