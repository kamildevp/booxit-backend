<?php

namespace App\Service\SetterHelper\Util;

use App\Exceptions\InvalidObjectException;
use App\Service\AttributeHelper\AttributeHelper;
use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Service\SetterHelper\Model\SetterMethod;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;

class SetterManager{

    public function __construct(private string $setterAttribute, private ContainerInterface $container)
    {
        
    }

    public function filterSetters(ReflectionClass $reflectionClass, $requestParameters):array
    {
        $classMethods = $reflectionClass->getMethods();

        foreach($classMethods as $method){
            $setterAttribute = (new AttributeHelper)->getUniqueAttribute($method, $this->setterAttribute);
            if(is_null($setterAttribute)){
                continue;
            }

            $setterAttributeArgs = $setterAttribute->getArguments();

            $targetParameter = $setterAttributeArgs['targetParameter'] ?? 
            (new DataHandlingHelper)->findLooseStringMatch($this->getSetterParameter($reflectionClass, $method), $requestParameters);


            if(!in_array($targetParameter, $requestParameters)){
                continue;
            }

            $setterMethod = new SetterMethod();
            $setterMethod->setTargetParameter($targetParameter);
            $setterMethod->setName($method->name);
            $setterTask = isset($setterAttributeArgs['setterTask']) ? $this->getSetterTaskInstance($setterAttributeArgs['setterTask']) : null;
            $setterMethod->setTask($setterTask);
            $setterMethod->setAliases($setterAttributeArgs['aliases'] ?? []);
            $setterMethods[] = $setterMethod;
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

    private function getSetterTaskInstance(string $taskName):SetterTaskInterface
    {
        if(!class_exists($taskName)){
            throw new InvalidObjectException("Setter task {$taskName} class does not exist");
        }

        $instance = $this->container->get($taskName);
        if(!($instance instanceof SetterTaskInterface)){
            throw new InvalidObjectException('Setter task class must implement SetterTaskInterface');
        }

        return $instance;
    } 
}