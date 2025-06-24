<?php

namespace App\DTO\User;

use App\DTO\ListOrderDTO;

class UserListOrderDTO extends ListOrderDTO {

    public function getOrderableColumns(): array
    {
        return ['name'];
    }
}