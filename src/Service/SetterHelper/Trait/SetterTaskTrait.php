<?php

namespace App\Service\SetterHelper\Trait;

use App\Exceptions\InvalidActionException;
use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Service\SetterHelper\Model\ParameterContainer;
use App\Service\SetterHelper\Model\TaskParameter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait SetterTaskTrait
{
    private object $object;
    private array $validationGroups = [];
    private array $validationErrors = [];
    /** @var Collection<int, TaskParameter> */
    private Collection $parameters;

    /** @return Collection<int, TaskParameter> */
    public function getTaskParameters():Collection
    {
        $parameters = new ArrayCollection();
        $dataHandlingHelper = new DataHandlingHelper();
        $preTaskParameters = $dataHandlingHelper->getMethodParameters($this, 'runPreValidation');
        $postTaskParameters = $dataHandlingHelper->getMethodParameters($this, 'runPostValidation');
        $taskParameters = array_merge($preTaskParameters, $postTaskParameters);

        foreach($taskParameters as $parameter){
            $taskParameter = new TaskParameter();
            $taskParameter->setName($parameter->name);
            $taskParameter->setRequired(!$parameter->isDefaultValueAvailable());
            $parameters->add($taskParameter);
        }
        $this->parameters = $parameters;
        return $parameters;
    }

    public function runPreValidationTask(array $args):void
    {
        $this->validationGroups = [];
        $this->validationErrors = [];

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
        return $this->validationGroups;
    }

    public function getValidationErrors():array
    {
        $validationErrors = [];
        foreach($this->validationErrors as $parameterName => $value){
            $key = $this->getParameterAlias($parameterName);
            $validationErrors[$key] = $value;
        }

        return $validationErrors;
    }

    public function runPreValidation():void
    {

    }

    public function runPostValidation():void
    {

    }

    private function getParameterAlias(string $parameterName){
        $parameter = $this->parameters->findFirst(function($key,$element) use ($parameterName){
            return $element->getName() === $parameterName;
        });
        
        if(!$parameter){
            return $parameterName;
        }

        $alias  = $parameter->getAlias();
        return $alias ? $alias : $parameterName;
    }


    

}