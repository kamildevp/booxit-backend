<?php

namespace App\Service\SetterHelper\Util;

abstract class SetterTask 
{
    private array $aliases;
    protected object $object;
    protected array $validationGroups;

    public function getParameterAlias(string $parameterName):string
    {
        return $this->aliases[$parameterName] ?? $parameterName;
    }

    public function getParameterName(string $alias):string
    {
        return array_flip($this->aliases)[$alias] ?? $alias;
    }

    public function setAliases(array $aliases):void
    {
        $this->aliases = $aliases;
    }

}