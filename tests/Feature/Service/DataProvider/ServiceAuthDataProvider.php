<?php

declare(strict_types=1);

namespace App\Tests\Feature\Service\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ServiceAuthDataProvider extends BaseDataProvider 
{
    public static function protectedPaths()
    {
        return [
            ['/api/services', 'POST'],
            ['/api/services/{service}', 'PATCH'],
            ['/api/services/{service}', 'DELETE'],
        ];
    }

    public static function privilegesOnlyPaths()
    {
        return [
            ['/api/services', 'POST', 'user1@example.com'],
            ['/api/services', 'POST', 'user1@example.com', ['organization_id' => 0]],
            ['/api/services', 'POST', 'user1@example.com', ['organization_id' => '{organization}']],
            ['/api/services', 'POST', 'om-user1@example.com', ['organization_id' => '{organization}']],
            ['/api/services/{service}', 'PATCH', 'user1@example.com'],
            ['/api/services/{service}', 'DELETE', 'user1@example.com'],
            ['/api/services/{service}', 'PATCH', 'om-user1@example.com'],
            ['/api/services/{service}', 'DELETE', 'om-user1@example.com'],
        ];
    }
}