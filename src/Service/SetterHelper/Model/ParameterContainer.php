<?php

namespace App\Service\SetterHelper\Model;


class ParameterContainer
{
    /** @var TaskParameter[] */
    private array $parameters = [];

    public function getParameters()
    {
        return $this->parameters;
    }


    public function addParameter(TaskParameter $parameter):void
    {
        $this->parameters[] = $parameter;            
    }



}
