<?php

namespace App\DTO\User;

use App\DTO\ListOrderDTO;
use App\Enum\TimestampsColumns;
use Nelmio\ApiDocBundle\Attribute\Ignore;
use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'object',
    properties: [
        new OA\Property(property: 'order', example: 'name,created_at,updated_at'),
        new OA\Property(property: 'order_dir', example: 'asc,desc,desc')
    ]
)]
class UserListOrderDTO extends ListOrderDTO 
{
    #[Ignore]
    public function getOrderableColumns(): array
    {
        return array_merge(TimestampsColumns::values(), ['name']);
    }
}