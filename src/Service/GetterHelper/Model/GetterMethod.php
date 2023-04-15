<?php

namespace App\Service\GetterHelper\Model;

use App\Service\GetterHelper\CustomAccessRule\CustomAccessRuleInterface;
use App\Service\GetterHelper\CustomFormat\CustomFormatInterface;

class GetterMethod
{
    private string $name;

    private string $targetProperty;

    private int|CustomAccessRuleInterface $accessRule;

    private ?CustomFormatInterface $format;

    private string $targetPropertyAlias;


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getTargetProperty(): ?string
    {
        return $this->targetProperty;
    }

    public function setTargetProperty(string $targetProperty)
    {
        $this->targetProperty = $targetProperty;
    }

    public function getAccessRule(): int|CustomAccessRuleInterface
    {
        return $this->accessRule;
    }

    public function setAccessRule(int|CustomAccessRuleInterface $accessRule)
    {
        $this->accessRule = $accessRule;
    }

    public function getFormat(): ?CustomFormatInterface
    {
        return $this->format;
    }

    public function setFormat(?CustomFormatInterface $format)
    {
        $this->format = $format;
    }

    public function getTargetPropertyAlias(): string
    {
        return $this->targetPropertyAlias;
    }

    public function setTargetPropertyAlias(string $targetPropertyAlias)
    {
        $this->targetPropertyAlias = $targetPropertyAlias;
    }
}
