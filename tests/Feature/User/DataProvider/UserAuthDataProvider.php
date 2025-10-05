<?php

declare(strict_types=1);

namespace App\Tests\Feature\User\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class UserAuthDataProvider extends BaseDataProvider 
{
    
    public static function protectedPaths()
    {
        return [
            ['/api/users/me', 'GET'],
            ['/api/users/me', 'PATCH'],
            ['/api/users/me/change-password', 'PATCH'],
            ['/api/users/me', 'DELETE'],
        ];
    }
}