<?php

namespace App\Service\GetterHelper\Util;

use App\Exceptions\InvalidConfigurationException;
use App\Exceptions\InvalidObjectException;
use App\Service\AttributeHelper\AttributeHelper;
use App\Service\GetterHelper\Attribute\Getter;
use App\Service\GetterHelper\CustomAccessRule\CustomAccessRuleInterface;
use App\Service\GetterHelper\Model\GetterMethod;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GetterManager{

    const ACCESS_RULES = [
        Getter::PUBLIC_ACCESS
    ];

    public function __construct(private string $getterAttribute, private ContainerInterface $container)
    {
        
    }

    public function filterGetters(ReflectionClass $reflectionClass):array
    {
        $classMethods = $reflectionClass->getMethods();

        foreach($classMethods as $method){
            $getterAttribute = (new AttributeHelper)->getUniqueAttribute($method, $this->getterAttribute);
            if(is_null($getterAttribute)){
                continue;
            }

            $getterAttributeArgs = $getterAttribute->getArguments();

            $targetProperty = $getterAttributeArgs['targetProperty'] ?? $this->getGetterTargetProperty($method);
            $accessRule = $getterAttributeArgs['accessRule'] ?? Getter::PUBLIC_ACCESS;
            $accessRule = is_int($accessRule) ? $accessRule : $this->getCustomAccessRuleInstance($getterAttributeArgs['accessRule']);
            if(!($accessRule instanceof CustomAccessRuleInterface) && !in_array($accessRule, self::ACCESS_RULES)){
                throw new InvalidConfigurationException("Getter attribute configured for {$reflectionClass->name}::{$method->name} has invalid access rule");
            } 

            $getterMethod = new GetterMethod();
            $getterMethod->setName($method->name);
            $getterMethod->setTargetProperty($targetProperty);
            $getterMethod->setAccessRule($accessRule);
            $getterMethods[] = $getterMethod;
        }
        return $getterMethods ?? [];
    }

    private function getGetterTargetProperty(ReflectionMethod $getter){
        $getterName = $getter->name;
        if(strlen($getterName) > 3){
            $propertyName = lcfirst(substr($getterName,3));
        }
        else{
            $propertyName = $getterName;
        }
        return $propertyName;
    }



    private function getCustomAccessRuleInstance(string $customAccessRule): CustomAccessRuleInterface
    {
        if(!class_exists($customAccessRule)){
            throw new InvalidObjectException("Custom access rule {$customAccessRule} class does not exist");
        }

        $instance = $this->container->get($customAccessRule);
        if(!($instance instanceof CustomAccessRuleInterface)){
            throw new InvalidObjectException('Custom access rule class must implement CustomAccessRuleInterface');
        }

        return $instance;
    } 
}