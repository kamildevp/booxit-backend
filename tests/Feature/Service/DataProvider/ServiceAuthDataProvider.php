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
            ['/api/services', 'POST'],
            ['/api/services/{service}', 'PATCH'],
            ['/api/services/{service}', 'DELETE'],
        ];
    }

    public static function serviceManagementPrivilegesOnlyPaths()
    {
        return [
            ['/api/services', 'POST', null],
            ['/api/services', 'POST', null, ['organization_id' => 0]],
            ['/api/services', 'POST', null, ['organization_id' => '{organization}']],
            ['/api/services', 'POST', OrganizationRole::MEMBER->value, ['organization_id' => '{organization}']],
            ['/api/services/{service}', 'PATCH', null],
            ['/api/services/{service}', 'DELETE', null],
            ['/api/services/{service}', 'PATCH', OrganizationRole::MEMBER->value],
            ['/api/services/{service}', 'DELETE', OrganizationRole::MEMBER->value],
        ];
    }
}