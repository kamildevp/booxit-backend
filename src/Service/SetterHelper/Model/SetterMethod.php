<?php

namespace App\Service\SetterHelper\Model;

use App\Service\SetterHelper\Task\SetterTaskInterface;

class SetterMethod
{
    private ?string $name = null;

    private ?string $targetParameter = null;

    private ?SetterTaskInterface $task = null;

    private ?array $aliases = [];


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getTargetParameter(): ?string
    {
        return $this->targetParameter;
    }

    public function setTargetParameter(string $targetParameter)
    {
        $this->targetParameter = $targetParameter;
    }

    public function getTask(): ?object
    {
        return $this->task;
    }

    public function setTask(?SetterTaskInterface $task)
    {
        $this->task = $task;
    }

    public function getAliases(): ?array
    {
        return $this->aliases;
    }

    public function setAliases(array $aliases)
    {
        $this->aliases = $aliases;
    }
}
