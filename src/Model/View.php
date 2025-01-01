<?php

namespace App\Model;

class View
{
    public function __construct(private string $template, private array $params)
    {
        
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function setParam(string $paramName, mixed $value): void
    {
        $this->params = array_merge($this->params, [$paramName => $value]);
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }
}
