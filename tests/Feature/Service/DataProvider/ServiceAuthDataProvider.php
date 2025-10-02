<?php

declare(strict_types=1);

namespace App\Tests\Feature\Service\DataProvider;

use App\Enum\Organization\OrganizationRole;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class ServiceAuthDataProvider extends BaseDataProvider 
{
    public static function protectedPaths()
    {
        return [
            ['/api/service', 'POST'],
            ['/api/service/{service}', 'PATCH'],
            ['/api/service/{service}', 'DELETE'],
        ];
    }

    public static function serviceAdminOnlyPaths()
    {
        return [
            ['/api/service/{service}', 'PATCH', null],
            ['/api/service/{service}', 'DELETE', null],
            ['/api/service/{service}', 'PATCH', OrganizationRole::MEMBER->value],
            ['/api/service/{service}', 'DELETE', OrganizationRole::MEMBER->value],
        ];
    }
}