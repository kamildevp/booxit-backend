<?php

namespace App\Service\DataHandlingHelper;

use DateTime;
use InvalidArgumentException;
use ReflectionMethod;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class DataHandlingHelper{

    public function mapMethodArguments(object $object, string $method, array $args):array
    {
        $mappedArgs = [];
        $parameters = $this->getMethodParameters($object, $method);

        foreach($parameters as $parameter){
            if(!key_exists($parameter->name, $args) && !$parameter->isDefaultValueAvailable()){
                throw new InvalidArgumentException("Parameter {$parameter->name} is required");
            }

            if(!key_exists($parameter->name, $args) && $parameter->isDefaultValueAvailable()){
                $mappedArgs[] = $parameter->getDefaultValue();
                continue;
            }

            $arg = $args[$parameter->name];
            if(!$parameter->hasType()){
                $mappedArgs[] = $arg;
                continue;
            }

            $parameterType = $parameter->getType();
            if($parameterType->isBuiltin()){
                settype($arg, $parameterType->getName());
                $mappedArgs[] = $arg;
            }    
        }
        return $mappedArgs;
    }

    public function getMethodParameters(object $object, string $method):array
    {
        if(!method_exists($object, $method)){
            $objectClass = get_class($object);
            throw new InvalidArgumentException("Method {$method} is not defined for object {$objectClass}");
        }
        $parameters = (new ReflectionMethod($object, $method))->getParameters();
        return $parameters;
    }

    public function replaceArrayKeys(array $array, array $keysReplacements):array
    {
        $orginalArray = $array;
        foreach($keysReplacements as $key => $newKey){
            if(!array_key_exists($key, $orginalArray)){
                continue;
            }

            unset($array[$key]);
            $array[$newKey] = $orginalArray[$key]; 
        }

        return $array;
    }

    public function findLooseStringMatch(string $string, array $stringArray, bool $matchSnakeCase = true, bool $matchCammelCase = true):?string
    {
        if(in_array($string, $stringArray)){
            return $string;
        }

        if($matchSnakeCase){
            $converter = new CamelCaseToSnakeCaseNameConverter();
            $snakeCase = $converter->normalize($string);
            if(in_array($snakeCase, $stringArray)){
                return $snakeCase;
            } 
        }

        if($matchCammelCase){
            $converter = new CamelCaseToSnakeCaseNameConverter();
            $cammelCase = $converter->denormalize($string);
            if(in_array($cammelCase, $stringArray)){
                return $cammelCase;
            } 
        }
        return null;
    }

    public function validateDateTime(string $dateTime, $format)
    {
        $dateTimeObject = DateTime::createFromFormat($format, $dateTime);
        return $dateTimeObject && $dateTimeObject->format($format) === $dateTime;
    }
}