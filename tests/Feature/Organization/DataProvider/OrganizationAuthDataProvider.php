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
            ['/api/organization', 'POST'],
            ['/api/organization/{organization}', 'PATCH'],
            ['/api/organization/{organization}', 'DELETE'],
            ['/api/organization/{organization}/banner', 'PUT'],
        ];
    }

    public static function organizationAdminOnlyPaths()
    {
        return [
            ['/api/organization/{organization}', 'PATCH', null],
            ['/api/organization/{organization}', 'DELETE', null],
            ['/api/organization/{organization}/banner', 'PUT', null],
            ['/api/organization/{organization}', 'PATCH', OrganizationRole::MEMBER->value],
            ['/api/organization/{organization}', 'DELETE', OrganizationRole::MEMBER->value],
            ['/api/organization/{organization}/banner', 'PUT', OrganizationRole::MEMBER->value],
        ];
    }
}