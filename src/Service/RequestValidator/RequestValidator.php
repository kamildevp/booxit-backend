<?php

namespace App\Service\RequestValidator;

use App\Exceptions\InvalidConfigurationException;
use App\Exceptions\InvalidRequestException;
use App\Service\DataHandlingHelper\DataHandlingHelper;

class RequestValidator{
    
    public function validateRequest(array $requestParameters, array $requiredParameters, array $optionalParameters = []){
        $definedParametersIntersect = array_intersect($requiredParameters, $optionalParameters);
        if(count($definedParametersIntersect)>0){
            $invalidParameter = array_values($definedParametersIntersect)[0];
            throw new InvalidConfigurationException("Parameter {$invalidParameter} declared as required and optional");
        }

        $requestParametersNames = array_keys($requestParameters);
        $dataHandlingHelper = new DataHandlingHelper();

        foreach($requiredParameters as $parameterName){
            $alias = $dataHandlingHelper->findLooseStringMatch($parameterName, $requestParametersNames);
            if(is_null($alias)){
                throw new InvalidRequestException("Parameter {$parameterName} is required");
            }
            $aliases[$alias] = $parameterName;
        }

        foreach($optionalParameters as $parameterName){
            $alias = $dataHandlingHelper->findLooseStringMatch($parameterName, $requestParametersNames);
            if(is_null($alias)){
                continue;
            }
            $aliases[$alias] = $parameterName;
        }
        $parsedParameters = $dataHandlingHelper->replaceArrayKeys($requestParameters, $aliases);

        $diff = array_diff(array_keys($parsedParameters), array_merge($requiredParameters, $optionalParameters));
        if(count($diff) > 0){
            $parameterName = array_values($diff)[0];
            throw new InvalidRequestException("Parameter {$parameterName} is not allowed");
        }

        return $parsedParameters;
    }

    
}