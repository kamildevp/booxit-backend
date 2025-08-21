<?php

namespace App\Documentation\Response;

use App\Enum\Interface\NormalizerGroupInterface;
use App\Enum\ResponseStatus;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class SuccessResponseDoc extends OA\Response
{
    public function __construct(
        int $statusCode = 200, 
        ?string $description = 'Success Response', 
        ?string $dataModel = null, 
        NormalizerGroupInterface|array|null $dataModelGroups = null, 
        ?OA\JsonContent $dataContent = null, 
        mixed $dataExample = Generator::UNDEFINED,
        array $headers = []
    )
    {
        $normalizerGroups = $dataModelGroups instanceof NormalizerGroupInterface ? $dataModelGroups->normalizationGroups() : $dataModelGroups;
        $dataProperty = !is_null($dataModel) ? 
            new OA\Property(property: 'data', ref: new Model(type: $dataModel, groups: $normalizerGroups)) : 
            new OA\Property(property: 'data', type: 'object', properties: $dataContent->properties ?? [], example: $dataExample);

        $content = new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "status", type: "string", example: ResponseStatus::SUCCESS->value),
                $dataProperty
            ]
        );

        parent::__construct(
            response: $statusCode,
            description: $description,
            content: $content,
            headers: $headers
        );
    }
}