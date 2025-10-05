<?php

declare(strict_types=1);

namespace App\Tests\Feature\OrganizationMember\DataProvider;

use App\Enum\Organization\OrganizationRole;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class OrganizationMemberAuthDataProvider extends BaseDataProvider 
{
    public static function protectedPaths()
    {
        return [
            ['/api/organizations/{organization}/members', 'POST'],
            ['/api/organizations/{organization}/members/{organizationMember}', 'DELETE'],
            ['/api/organizations/{organization}/members/{organizationMember}', 'PATCH'],
        ];
    }

    public static function organizationManagementPrivilegesOnlyPaths()
    {
        return [
            ['/api/organizations/{organization}/members', 'POST', null],
            ['/api/organizations/{organization}/members/{organizationMember}', 'DELETE', null],
            ['/api/organizations/{organization}/members/{organizationMember}', 'PATCH', null],
            ['/api/organizations/{organization}/members', 'POST', OrganizationRole::MEMBER->value],
            ['/api/organizations/{organization}/members/{organizationMember}', 'DELETE', OrganizationRole::MEMBER->value],
            ['/api/organizations/{organization}/members/{organizationMember}', 'PATCH', OrganizationRole::MEMBER->value],
        ];
    }
}