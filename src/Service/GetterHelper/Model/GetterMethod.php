<?php

namespace App\Service\GetterHelper\Model;

use App\Service\GetterHelper\CustomAccessRule\CustomAccessRuleInterface;

class GetterMethod
{
    private string $name;

    private string $targetProperty;

    private int|CustomAccessRuleInterface $accessRule;



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
}
