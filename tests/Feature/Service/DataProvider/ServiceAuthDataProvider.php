<?php

declare(strict_types=1);

namespace App\Tests\Feature\Service\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ServiceAuthDataProvider extends BaseDataProvider 
{
    public static function protectedPaths()
    {
        return [
            ['/api/organizations/{organization}/services', 'POST'],
            ['/api/organizations/{organization}/services/{service}', 'PATCH'],
            ['/api/organizations/{organization}/services/{service}', 'DELETE'],
        ];
    }

    public static function privilegesOnlyPaths()
    {
        return [
            ['/api/organizations/{organization}/services', 'POST', 'user1@example.com'],
            ['/api/organizations/{organization}/services', 'POST', 'om-user1@example.com'],
            ['/api/organizations/{organization}/services/{service}', 'PATCH', 'user1@example.com'],
            ['/api/organizations/{organization}/services/{service}', 'PATCH', 'om-user1@example.com'],
            ['/api/organizations/{organization}/services/{service}', 'DELETE', 'user1@example.com'],
            ['/api/organizations/{organization}/services/{service}', 'DELETE', 'om-user1@example.com'],
        ];
    }
}