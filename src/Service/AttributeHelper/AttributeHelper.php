<?php

namespace App\Service\AttributeHelper;

use App\Exceptions\InvalidObjectException;
use ReflectionAttribute;
use ReflectionMethod;

class AttributeHelper{

    public function getUniqueAttribute(ReflectionMethod $method, string $attributeClass):?ReflectionAttribute
    {
        $attributes = $method->getAttributes($attributeClass);
        switch(count($attributes)){
            case 0:
                break;
            case 1:
                break;
            default:
                throw new InvalidObjectException("Object {$method->class} can only have one {$attributeClass} attribute defined");
        }
        return $attributes[0] ?? null;
    }
}