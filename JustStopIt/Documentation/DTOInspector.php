<?php

namespace App\Service\Documentation;

use App\Kernel;
use App\Service\Documentation\MessageResolver\UniqueEntityFieldMessageResolver;
use App\Validator\Constraints\UniqueEntityField;
use ReflectionAttribute;
use ReflectionClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;

class DTOInspector
{
    protected ReflectionClass $dtoReflection;

    protected array $constraintsConfig;

    public function __construct(protected Kernel $kernel)
    {
        $this->constraintsConfig = [
            'default' => [
                'messages' => [
                    [
                        'name' => 'message',
                        'substitutes' => []
                    ],
                ],
            ],
            Length::class => [
                'messages' => [
                    [
                        'name' => 'minMessage',
                        'substitutes' => [
                            'limit' => 'min'
                        ]
                    ],
                    [
                        'name' => 'maxMessage',
                        'substitutes' => [
                            'limit' => 'max'
                        ]
                    ]
                ],
            ],
            UniqueEntityField::class => [
                'messageResolver' => UniqueEntityFieldMessageResolver::class
            ],
        ];  
    }

    public function getValidationErrors(string $dtoClass){
        $errors = [];
        $dtoReflection = new ReflectionClass($dtoClass); 

        foreach($dtoReflection->getProperties() as $reflectionProperty){
            $propertyAttributes = $reflectionProperty->getAttributes(Constraint::class, ReflectionAttribute::IS_INSTANCEOF);
            $propertyName = $reflectionProperty->getName();
            foreach($propertyAttributes as $constraint){   
                $errors[$propertyName] = array_merge($errors[$propertyName] ?? [], $this->getConstraintMessages($constraint));
            }
        }

        return $errors;
    }

    protected function getConstraintMessages(ReflectionAttribute $constraint): array
    {
        $constraintConfig = $this->constraintsConfig[$constraint->getName()] ?? $this->constraintsConfig['default'];
        if(array_key_exists('messageResolver', $constraintConfig)){
            return $this->kernel->getContainer()->get($constraint['messageResolver'])->resolveMessages($constraint);
        }

        $messagesConfig = $constraintConfig['messages'];
        $constraintInstance = $constraint->newInstance();

        return array_map(
            fn($messageConfig) => $this->evaluateMessage(
                $constraintInstance->{$messageConfig['name']}, 
                $messageConfig['substitutes']
            ), 
        $messagesConfig);
    }

    protected function evaluateMessage(string $messageTemplate, array $substitutes): string
    {
        $message = $messageTemplate;
        foreach($substitutes as $placeholder => $value){
            $message = str_replace("{{ $placeholder }}", $value, $message);
        }

        return $message;
    }
}