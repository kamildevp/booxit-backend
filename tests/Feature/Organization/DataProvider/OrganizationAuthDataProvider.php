<?php

declare(strict_types=1);

namespace App\Tests\Feature\Organization\DataProvider;

use App\Enum\Organization\OrganizationRole;
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

    public static function organizationAdminOnlyPaths()
    {
        return [
            ['/api/organizations/{organization}', 'PATCH', null],
            ['/api/organizations/{organization}', 'DELETE', null],
            ['/api/organizations/{organization}/banner', 'PUT', null],
            ['/api/organizations/{organization}', 'PATCH', OrganizationRole::MEMBER->value],
            ['/api/organizations/{organization}', 'DELETE', OrganizationRole::MEMBER->value],
            ['/api/organizations/{organization}/banner', 'PUT', OrganizationRole::MEMBER->value],
        ];
    }
}