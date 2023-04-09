<?php

namespace App\Service\SetterHelper\Trait;

use App\Exceptions\InvalidActionException;
use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Service\SetterHelper\Model\ParameterContainer;
use App\Service\SetterHelper\Model\TaskParameter;

trait SetterTaskTrait
{
    private object $object;
    private array $validationGroups;

    public function getTaskParameters():ParameterContainer
    {
        $parameterContainer = new ParameterContainer();
        $dataHandlingHelper = new DataHandlingHelper();
        $preTaskParameters = $dataHandlingHelper->getMethodParameters($this, 'runPreValidation');
        $postTaskParameters = $dataHandlingHelper->getMethodParameters($this, 'runPostValidation');
        $parameters = array_merge($preTaskParameters, $postTaskParameters);

        foreach($parameters as $parameter){
            $taskParameter = new TaskParameter();
            $taskParameter->setName($parameter->name);
            $taskParameter->setRequired(!$parameter->isDefaultValueAvailable());
            $parameterContainer->addParameter($taskParameter);
        }
        return $parameterContainer;
    }

    public function runPreValidationTask(array $args):void
    {
        if(!$this->object){
            throw new InvalidActionException('Setter task object has not been set');
        }

        $dataHandlingHelper = new DataHandlingHelper();
        $mappedArgs = $dataHandlingHelper->mapMethodArguments($this, 'runPreValidation', $args);
        $this->runPreValidation(...$mappedArgs);
    }

    public function runPostValidationTask(array $args):void
    {
        if(!$this->object){
            throw new InvalidActionException('Setter task object has not been set');
        }

        $dataHandlingHelper = new DataHandlingHelper();
        $mappedArgs = $dataHandlingHelper->mapMethodArguments($this, 'runPostValidation', $args);
        $this->runPostValidation(...$mappedArgs);
    }

    public function setObject(object $object): void
    {
        $this->object = $object;   
    }

    public function getValidationGroups():array
    {
        return $this->validationGroups ?? [];
    }

    public function runPreValidation():void
    {

    }

    public function runPostValidation():void
    {

    }


    

}