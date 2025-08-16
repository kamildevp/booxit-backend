<?php

namespace App\DTO\User;

use App\DTO\ListOrderDTO;
use App\Enum\TimestampsColumns;

class UserListOrderDTO extends ListOrderDTO 
{
    public function getOrderableColumns(): array
    {
        return array_merge(TimestampsColumns::values(), ['name']);
    }
}