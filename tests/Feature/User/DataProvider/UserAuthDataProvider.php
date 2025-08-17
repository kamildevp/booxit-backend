<?php

namespace App\Tests\Feature\User\DataProvider;

use App\Tests\Feature\Global\DataProvider\BaseDataProvider;

class UserAuthDataProvider extends BaseDataProvider 
{
    
    public static function protectedPaths()
    {
        return [
            ['/api/user/me', 'GET'],
            ['/api/user/me', 'PATCH'],
            ['/api/user/change_password', 'PATCH'],
            ['/api/user/me', 'DELETE'],
        ];
    }
}