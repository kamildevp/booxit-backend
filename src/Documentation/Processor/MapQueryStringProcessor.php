<?php

declare(strict_types=1);

namespace App\Documentation\Processor;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber\SymfonyMapQueryStringDescriber;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class MapQueryStringProcessor
{
    public function __invoke(Analysis $analysis): void
    {
        /** @var OA\Operation[] $operations */
        $operations = $analysis->getAnnotationsOfType(OA\Operation::class);

        foreach ($operations as $operation) {
            if (!isset($operation->_context->{SymfonyMapQueryStringDescriber::CONTEXT_KEY})) {
                continue;
            }

            $mapQueryStringContexts = $operation->_context->{SymfonyMapQueryStringDescriber::CONTEXT_KEY};
            if (!\is_array($mapQueryStringContexts)) {
                throw new \LogicException(\sprintf('MapQueryString contexts not found for operation "%s"', $operation->operationId));
            }

            foreach ($mapQueryStringContexts as $mapQueryStringContext) {
                $this->addQueryParameters($analysis, $operation, $mapQueryStringContext);
            }
        }
    }

    /**
     * @param array<string, mixed> $mapQueryStringContext
     */
    private function addQueryParameters(Analysis $analysis, OA\Operation $operation, array $mapQueryStringContext): void
    {
        $argumentMetaData = $mapQueryStringContext[SymfonyMapQueryStringDescriber::CONTEXT_ARGUMENT_METADATA];
        if (!$argumentMetaData instanceof ArgumentMetadata) {
            throw new \LogicException(\sprintf('MapQueryString ArgumentMetaData not found for operation "%s"', $operation->operationId));
        }

        $modelRef = $mapQueryStringContext[SymfonyMapQueryStringDescriber::CONTEXT_MODEL_REF];
        if (!isset($modelRef)) {
            throw new \LogicException(\sprintf('MapQueryString Model reference not found for operation "%s"', $operation->operationId));
        }

        $nativeModelName = str_replace(OA\Components::SCHEMA_REF, '', $modelRef);
        $schemaModel = Util::getSchema($analysis->openapi, $nativeModelName);
        $this->addSchemaAsSeparatedParameters($analysis, $schemaModel, $operation);
    }


    private function addSchemaAsSeparatedParameters(Analysis $analysis, Schema $schemaModel, OA\Operation $operation, string $parameterPrefix = '')
    {
        if (Generator::UNDEFINED === $schemaModel->properties) {
            return;
        }
        
        foreach ($schemaModel->properties as $property) {
            $name = !empty($parameterPrefix) ? $parameterPrefix.'['.$property->property.']' : $property->property;
            $name = 'array' === $property->type ? $name .'[]' : $name;

            // Remove incompatible properties
            $propertyVars = get_object_vars($property);
            unset($propertyVars['property']);
            $schema = new OA\Schema($propertyVars);

            if($schema->type != Generator::UNDEFINED){
                $description = $this->replaceDescriptionPlaceholders($schema->description);
                $operationParameter = Util::getOperationParameter($operation, $name, 'query');
                $operationParameter->schema = $schema;
                $operationParameter->name = $name;
                $operationParameter->description = $description;
                $operationParameter->required = $schema->required;
                $operationParameter->deprecated = $schema->deprecated;
                $operationParameter->example = $schema->example;

                if (\is_array($schemaModel->required) && \in_array($property->property, $schemaModel->required, true)) {
                    Util::modifyAnnotationValue($operationParameter, 'required', true);
                } else {
                    Util::modifyAnnotationValue($operationParameter, 'required', false);
                }

                continue;
            }

            $this->removeOperationParameter($operation, $name, 'query');
            $ref = isset($schema->oneOf[0]->ref) ? $schema->oneOf[0]->ref : $schema->ref;
            $modelName = str_replace(OA\Components::SCHEMA_REF, '', $ref);
            $refSchema = Util::getSchema($analysis->openapi, $modelName);

            $this->addSchemaAsSeparatedParameters($analysis, $refSchema, $operation, $name);
        }
    }

    private function removeOperationParameter(OA\Operation $operation, string $name, string $in): void
    {
        $key = null;
        $nested = $operation::$_nested;
        $collection = $nested[OA\Parameter::class][0];

        $key = Util::searchCollectionItem(
            $operation->{$collection} && Generator::UNDEFINED !== $operation->{$collection} ? $operation->{$collection} : [],
            ['name' => $name, 'in' => $in]
        );
        
        if (null === $key) {
            return;
        }

        unset($operation->{$collection}[$key]);
        $operation->{$collection} = array_values($operation->{$collection});
    }

    private function replaceDescriptionPlaceholders(string $description): string
    {
        $matches = [];
        preg_match_all('/(?<=\{\{ )([\p{L}\p{N}\\\\]+::[\p{L}\p{N}]+)(?= \}\})/u', $description, $matches);

        $placeholders = $matches[0];
        foreach($placeholders as $placeholder){
            $callable = explode("::", $placeholder);
            if(is_callable($callable)){
                $value = call_user_func($callable);
                $stringValue = is_array($value) ? implode(', ', $value) : (string)$value;
                $description = str_replace("{{ $placeholder }}", $stringValue, $description);
            }
        }

        return $description;
    }
}
