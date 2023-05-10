<?php

namespace App\Service\SetterHelper\Util;

use App\Service\DataHandlingHelper\DataHandlingHelper;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class RequestParser
{
    private array $requestErrors = []; 

    public function parseRequestParameters(array $setterMethods, array $requestParameters):void
    {
        $usedParameters = [];
        foreach($setterMethods as $setterMethod){
            $usedParameters[] = $setterMethod->getTargetParameter();
            $setterTask = $setterMethod->getTask();

            if(!$setterTask){
                continue;
            }

            $parameters = $this->parseTaskParameters($setterTask, $requestParameters, $setterMethod->getAliases());
            $taskAliases = $this->mapTaskAliases($parameters);
            $setterMethod->setAliases($taskAliases);
            $usedParameters = array_merge($usedParameters, $taskAliases);
        }

        foreach($requestParameters as $parameterName){
            if(!in_array($parameterName, $usedParameters)){
                $this->requestErrors[$parameterName] = "Parameter is not allowed";
            }
        }
    }


    private function parseTaskParameters($task, $requestParameters, $setterAliases):Collection
    {
        $parameters = $task->getTaskParameters();

        foreach($parameters as $parameter){
            $parameterName = $parameter->getName();
            $parameterName = $setterAliases[$parameterName] ?? (new CamelCaseToSnakeCaseNameConverter())->normalize($parameterName);

            $alias = (new DataHandlingHelper)->findLooseStringMatch($parameterName, $requestParameters);
            if(is_null($alias) && $parameter->isRequired())
            {
                $this->requestErrors[$parameterName] = "Parameter is required";
            }

            $parameter->setAlias($alias ?? $parameterName);
        }
        return $parameters;
    }

    private function mapTaskAliases(Collection $parameters):array
    {
        $aliases = [];
        foreach($parameters as $parameter){
            $parameterName = $parameter->getName();
            $aliases[$parameterName] = $parameter->getAlias();
        }
        return $aliases;
    }

    public function getRequestErrors():array
    {
        return $this->requestErrors;
    }
}