<?php

declare(strict_types=1);

namespace App\Repository\ORM\Filter;

use App\Repository\ORM\Attribute\Verifiable;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class VerifiableFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        $verifiableAttributes = $targetEntity->reflClass->getAttributes(Verifiable::class);
        if(empty($verifiableAttributes)){
            return '';
        }

        $fieldName = $verifiableAttributes[0]->newInstance()->fieldName;
        if (empty($fieldName)) {
            return '';
        }

        return sprintf('%s.%s = true', $targetTableAlias, $fieldName);
    }
}