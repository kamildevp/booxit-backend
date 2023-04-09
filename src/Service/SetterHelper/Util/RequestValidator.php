<?php

namespace App\Service\SetterHelper\Util;

use App\Exceptions\InvalidRequestException;
use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Service\SetterHelper\Model\ParameterContainer;

class RequestValidator{

    public function validateRequestParameters(array $setterMethods, array $requestParameters):void
    {
        $usedParameters = [];
        foreach($setterMethods as $setterMethod){
            $usedParameters[] = $setterMethod->getTargetParameter();
            $setterTask = $setterMethod->getTask();

            if(!$setterTask){
                continue;
            }

            $parameterContainer = $this->parseTaskParameters($setterTask, $requestParameters, $setterMethod->getAliases());
            $taskAliases = $this->getTaskAliases($parameterContainer);
            $setterMethod->setAliases($taskAliases);
            $usedParameters = array_merge($usedParameters, $taskAliases);
        }

        foreach($requestParameters as $parameter){
            if(!in_array($parameter, $usedParameters)){
                throw new InvalidRequestException("Request parameter {$parameter} is not allowed");
            }
        }
    }


    private function parseTaskParameters($task, $requestParameters, $setterAliases):ParameterContainer
    {
            $parameterContainer = $task->getTaskParameters();
            $parameters = $parameterContainer->getParameters();

            foreach($parameters as $parameter){
                $parameterName = $parameter->getName();
                if(in_array($parameterName, $requestParameters)){
                    continue;
                }

                $setterAlias = $setterAliases[$parameterName] ?? null;
                if(!is_null($setterAlias))
                {
                    if(!in_array($setterAlias, $requestParameters))
                    {
                        throw new InvalidRequestException("Alias defined for {$parameterName} not found in request parameters");
                    }
                    $parameter->setAlias($setterAlias);
                    continue;
                }
    
                $alias = (new DataHandlingHelper)->findLooseStringMatch($parameterName, $requestParameters);
                if(is_null($alias))
                {
                    throw new InvalidRequestException("Parameter {$parameterName} is required");
                }
                $parameter->setAlias($alias);
    
            }
            return $parameterContainer;
    }

    private function getTaskAliases(ParameterContainer $parameterContainer):array
    {
        $parameters = $parameterContainer->getParameters();
        $aliases = [];
        foreach($parameters as $parameter){
            $alias = $parameter->getAlias();
            if(is_null($alias)){
                continue;
            }
            $parameterName = $parameter->getName();
            $aliases[$parameterName] = $alias;
        }
        return $aliases;
    }
}