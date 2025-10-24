<?php

namespace App\Service\Documentation\MessageResolver;

use App\Validator\Constraints\UniqueEntityField;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraint;

class UniqueEntityFieldMessageResolver 
{
    public function resolveMessages(Constraint $constraint): array
    {
        if(!$constraint instanceof UniqueEntityField){
            throw new InvalidArgumentException('Unsupported constraint type');
        }

        $entityName = (new \ReflectionClass($constraint->entityClass))->getShortName();
        $message = str_replace(['{{ entityClass }}', '{{ fieldName }}'], [$entityName, $constraint->fieldName], $constraint->message);


        return [$message]; 
    }
}