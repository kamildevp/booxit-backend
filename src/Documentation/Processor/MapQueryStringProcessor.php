<?php

declare(strict_types=1);

namespace App\Documentation\Processor;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber\SymfonyMapQueryStringDescriber;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
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

        // There are no properties to map to query parameters
        if (Generator::UNDEFINED === $schemaModel->properties) {
            return;
        }

        foreach ($schemaModel->properties as $property) {
            $name = 'array' === $property->type
                ? $property->property.'[]'
                : $property->property;

            $operationParameter = Util::getOperationParameter($operation, $name, 'query');

            Util::modifyAnnotationValue($operationParameter, 'explode', false);
            Util::modifyAnnotationValue($operationParameter, 'style', $operationParameter->schema->type == Generator::UNDEFINED ? 'deepObject' : Generator::UNDEFINED);
        }
    }
}
