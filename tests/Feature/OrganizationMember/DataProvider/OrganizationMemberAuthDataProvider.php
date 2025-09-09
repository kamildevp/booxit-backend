<?php

declare(strict_types=1);

namespace App\Tests\Feature\OrganizationMember\DataProvider;

use App\Enum\Organization\OrganizationRole;
use App\Tests\Feature\Global\DataProvider\BaseDataProvider;

class OrganizationMemberAuthDataProvider extends BaseDataProvider 
{
    public static function protectedPaths()
    {
        return [
            ['/api/organization/{organization}/member', 'POST'],
            ['/api/organization-member/{organizationMember}', 'DELETE'],
            ['/api/organization-member/{organizationMember}', 'PATCH'],
        ];
    }

    public static function organizationAdminOnlyPaths()
    {
        return [
            ['/api/organization/{organization}/member', 'POST', null],
            ['/api/organization-member/{organizationMember}', 'DELETE', null],
            ['/api/organization-member/{organizationMember}', 'PATCH', null],
            ['/api/organization/{organization}/member', 'POST', OrganizationRole::MEMBER->value],
            ['/api/organization-member/{organizationMember}', 'DELETE', OrganizationRole::MEMBER->value],
            ['/api/organization-member/{organizationMember}', 'PATCH', OrganizationRole::MEMBER->value],
        ];
    }
}