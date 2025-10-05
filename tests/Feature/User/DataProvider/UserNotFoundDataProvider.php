<?php

declare(strict_types=1);

namespace App\Tests\Feature\User\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class UserNotFoundDataProvider extends BaseDataProvider 
{
    public static function dataCases()
    {
        return [
            [
                '/api/users/1000',
                'GET',
            ],
            [
                '/api/users/1000/organization-memberships',
                'GET',
            ],
        ];
    }
}