<?php

namespace App\Service\SetterHelper\Util;

use App\Exceptions\InvalidObjectException;
use App\Exceptions\InvalidRequestException;
use App\Service\AttributeHelper\AttributeHelper;
use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Service\ObjectHandlingHelper\ObjectHandlingHelper;
use App\Service\SetterHelper\Model\SetterMethod;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Validator\Test\ConstraintViolationAssertion;

class SetterManager{

    public function __construct(private string $setterAttribute, private ObjectHandlingHelper $objectHandlingHelper)
    {
        
    }

    public function filterSetters(ReflectionClass $reflectionClass, $requestParameters, bool $selectAll, array $filterGroups):array
    {
        $classMethods = $reflectionClass->getMethods();

        foreach($classMethods as $method){
            $setterAttribute = (new AttributeHelper)->getUniqueAttribute($method, $this->setterAttribute);
            if(is_null($setterAttribute)){
                continue;
            }
            $setterAttributeInstance = $setterAttribute->newInstance();

            $setterGroups = $setterAttributeInstance->groups;
            if(empty(array_intersect($filterGroups, $setterGroups))){
                continue;
            }

            $targetProperty = $this->getSetterParameter($reflectionClass, $method);
            $targetParameter = $setterAttributeInstance->targetParameter ?? 
            (new DataHandlingHelper)->findLooseStringMatch($targetProperty, $requestParameters);

            if($selectAll && !in_array($targetParameter, $requestParameters)){
                $targetParameter = $targetParameter ?? $targetProperty;
                throw new InvalidRequestException("Parameter {$targetParameter} is required");
            }

            if(!$selectAll && !in_array($targetParameter, $requestParameters)){
                continue;
            }

            $setterMethod = new SetterMethod();
            $setterMethod->setTargetProperty($targetProperty);
            $setterMethod->setTargetParameter($targetParameter);
            $setterMethod->setName($method->name);
            $setterTask = isset($setterAttributeInstance->setterTask) ? $this->objectHandlingHelper->getClassInstance($setterAttributeInstance->setterTask, SetterTaskInterface::class) : null;
            $setterMethod->setTask($setterTask);
            $setterMethod->setAliases($setterAttributeInstance->aliases);
            $setterMethods[$targetProperty] = $setterMethod;
        }
        return $setterMethods ?? [];
    }

    private function getSetterParameter(ReflectionClass $class, ReflectionMethod $setter):string
    {
        $setterParameters = $setter->getParameters();
        if(count($setterParameters) !== 1){
            throw new InvalidObjectException("Object setter {$setter->name} has more than one parameter");
        }

        $propertyName = $setterParameters[0]->getName();
        if(!$class->hasProperty($propertyName)){
            throw new InvalidObjectException("Cannot find property matching object setter {$setter->name}");
        }
        return $propertyName;
    }

}