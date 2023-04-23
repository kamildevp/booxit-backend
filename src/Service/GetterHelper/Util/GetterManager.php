<?php

namespace App\Service\GetterHelper\Util;

use App\Exceptions\InvalidConfigurationException;
use App\Service\AttributeHelper\AttributeHelper;
use App\Service\GetterHelper\Attribute\Getter;
use App\Service\GetterHelper\CustomAccessRule\CustomAccessRuleInterface;
use App\Service\GetterHelper\Model\GetterMethod;
use App\Service\ObjectHandlingHelper\ObjectHandlingHelper;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class GetterManager{

    const ACCESS_RULES = [
        Getter::PUBLIC_ACCESS
    ];

    public function __construct(private string $getterAttribute, private ObjectHandlingHelper $objectHandlingHelper)
    {
        
    }

    public function filterGetters(ReflectionClass $reflectionClass, array $filterGroups):array
    {
        $classMethods = $reflectionClass->getMethods();

        foreach($classMethods as $method){
            $getterAttribute = (new AttributeHelper)->getUniqueAttribute($method, $this->getterAttribute);
            if(is_null($getterAttribute)){
                continue;
            }
            $getterAttributeInstance = $getterAttribute->newInstance();

            $getterGroups = $getterAttributeInstance->groups;
            if(empty(array_intersect($filterGroups, $getterGroups))){
                continue;
            }

            $targetProperty = $this->getGetterTargetProperty($method);
            $accessRule = $getterAttributeInstance->accessRule;
            $accessRule = is_int($accessRule) ? $accessRule : ($this->objectHandlingHelper->getClassInstance($accessRule));
            if(!($accessRule instanceof CustomAccessRuleInterface) && !in_array($accessRule, self::ACCESS_RULES)){
                throw new InvalidConfigurationException("Getter attribute configured for {$reflectionClass->name}::{$method->name} has invalid access rule");
            }
            $format = $getterAttributeInstance->format;
            $format = !is_null($format) ? $this->objectHandlingHelper->getClassInstance($format) : null;
            $propertyNameAlias = $getterAttributeInstance->propertyNameAlias;
            $targetPropertyAlias = is_string($propertyNameAlias) ? $propertyNameAlias : $this->getTargetPropertyAlias($targetProperty, $propertyNameAlias);

            $getterMethod = new GetterMethod();
            $getterMethod->setName($method->name);
            $getterMethod->setTargetProperty($targetProperty);
            $getterMethod->setAccessRule($accessRule);
            $getterMethod->setFormat($format);
            $getterMethod->setTargetPropertyAlias($targetPropertyAlias);

            $getterMethods[] = $getterMethod;
        }
        return $getterMethods ?? [];
    }

    private function getGetterTargetProperty(ReflectionMethod $getter){
        $getterName = $getter->name;

        switch(true){
            case strlen($getterName) <= 3:
                $propertyName = $getterName;
                break;
            case str_starts_with($getterName, 'get'):
                $propertyName = lcfirst(substr($getterName,3));
                break;
            case str_starts_with($getterName, 'is'):
                $propertyName = lcfirst(substr($getterName,2));
                break;
            default:
                $propertyName = $getterName;
        }
        
        return $propertyName;
    }

    private function getTargetPropertyAlias(string $targetProperty, int $mappingType){

        $converter = new CamelCaseToSnakeCaseNameConverter();
        switch($mappingType){
            case Getter::SNAKE_CASE:
                return $converter->normalize($targetProperty);
                break;
            case Getter::CAMMEL_CASE:
                return $converter->denormalize($targetProperty);
                break;
            default:
                return $targetProperty;
        }
    }



    
}