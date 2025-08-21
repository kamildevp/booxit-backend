<?php

namespace App\Documentation\Response;

use App\Enum\Interface\NormalizerGroupInterface;
use App\Enum\ResponseStatus;
use App\Repository\Pagination\Model\PaginationResult;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class PaginatorResponseDoc extends SuccessResponseDoc
{
    public function __construct(
        ?string $description = null, 
        ?string $dataModel = null, 
        NormalizerGroupInterface|array|null $dataModelGroups = null, 
        ?OA\Items $itemSchema = null, 
        array $headers = []
    )
    {
        $normalizerGroups = $dataModelGroups instanceof NormalizerGroupInterface ? $dataModelGroups->normalizationGroups() : $dataModelGroups;
        $dataProperty = new OA\Property(property: 'data', allOf: [
            new OA\Schema(ref: new Model(type: PaginationResult::class)),
            new OA\Schema(type: 'object', properties: [
                new OA\Property(
                    property: 'items',      
                    type: 'array',     
                    items: !is_null($dataModel) ? new OA\Items(ref: new Model(type: $dataModel, groups: $normalizerGroups)) : $itemSchema
                )
            ])
        ]); 

        $dataContent = new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "status", type: "string", example: ResponseStatus::SUCCESS->value),
                $dataProperty
            ]
        );

        parent::__construct(
            description: $description,
            dataContent: $dataContent,
            headers: $headers
        );
    }
}