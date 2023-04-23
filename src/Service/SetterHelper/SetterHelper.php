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
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class SetterHelper implements SetterHelperInterface
{
    const SETTER_ATTRIBUTE = Setter::class;

    private array $setterMethods = [];
    private array $settings = [];
    private array $validationGroups = ['Default'];
    private array $validationErrors = [];

    public function __construct(private Kernel $kernel)
    {

    }

    public function updateObjectSettings(object $object, array $settings, array $requiredGroups = [], array $optionalGroups = ['Default']):void
    {
        $reflectionClass = new ReflectionClass($object);
        if(empty($settings)){
            $nameComponents = explode('\\', $reflectionClass->getName());
            $objectName = end($nameComponents);
            $objectName = (new CamelCaseToSnakeCaseNameConverter())->normalize($objectName);
            throw new InvalidRequestException("Request has no parameters for {$objectName}");
        }

        
        $requestParameters = array_keys($settings);
        $setterManager = new SetterManager(self::SETTER_ATTRIBUTE, new ObjectHandlingHelper($this->kernel));
        $this->setterMethods = $setterManager->filterSetters($reflectionClass, $requestParameters, $requiredGroups, $optionalGroups);
        (new RequestParser)->parseRequestParameters($this->setterMethods, $requestParameters);

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
            $this->validationErrors = array_merge($this->validationErrors, $task->getValidationErrors());
        }
        $this->mapValidationErrors();
        $this->settings = $settings;
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

    private function mapValidationErrors(){
        $requestParametersNames = [];
        foreach($this->validationErrors as $propertyName => $message){
            $requestParametersNames[$propertyName] = $this->getPropertyRequestParameter($propertyName);
        }

        $this->validationErrors = (new DataHandlingHelper)->replaceArrayKeys($this->validationErrors, $requestParametersNames);
    }

}