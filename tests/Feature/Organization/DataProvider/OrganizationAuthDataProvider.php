<?php

declare(strict_types=1);

namespace App\Tests\Feature\Organization\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class OrganizationAuthDataProvider extends BaseDataProvider 
{
    public static function protectedPaths()
    {
        return [
            ['/api/organizations', 'POST'],
            ['/api/organizations/{organization}', 'PATCH'],
            ['/api/organizations/{organization}', 'DELETE'],
            ['/api/organizations/{organization}/banner', 'PUT'],
        ];
    }

    public static function privilegesOnlyPaths()
    {
        return [
            ['/api/organizations/{organization}', 'PATCH', 'user1@example.com'],
            ['/api/organizations/{organization}', 'DELETE', 'user1@example.com'],
            ['/api/organizations/{organization}/banner', 'PUT', 'user1@example.com'],
            ['/api/organizations/{organization}', 'PATCH', 'om-user1@example.com'],
            ['/api/organizations/{organization}', 'DELETE', 'om-user1@example.com'],
            ['/api/organizations/{organization}/banner', 'PUT', 'om-user1@example.com'],
        ];
    }
}