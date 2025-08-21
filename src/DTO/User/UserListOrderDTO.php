<?php

namespace App\DTO\User;

use App\DTO\ListOrderDTO;
use App\Enum\TimestampsColumns;
use Nelmio\ApiDocBundle\Attribute\Ignore;

class UserListOrderDTO extends ListOrderDTO 
{
    #[Ignore]
    public function getOrderableColumns(): array
    {
        return array_merge(TimestampsColumns::values(), ['name']);
    }
}