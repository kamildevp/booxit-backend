<?php

declare(strict_types=1);

namespace App\Tests\Feature\OrganizationMember\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class OrganizationMemberAuthDataProvider extends BaseDataProvider 
{
    public static function protectedPaths()
    {
        return [
            ['/api/organizations/{organization}/members', 'POST'],
            ['/api/organizations/{organization}/members/{organizationMember}', 'DELETE'],
            ['/api/organizations/{organization}/members/{organizationMember}', 'PATCH'],
            ['/api/organizations/{organization}/members/{organizationMember}/schedule-assignments', 'GET'],
        ];
    }

    public static function privilegesOnlyPaths()
    {
        return [
            ['/api/organizations/{organization}/members', 'POST', 'user1@example.com'],
            ['/api/organizations/{organization}/members', 'POST', 'om-user1@example.com'],
            ['/api/organizations/{organization}/members/{organizationMember}', 'PATCH', 'user1@example.com'],
            ['/api/organizations/{organization}/members/{organizationMember}', 'PATCH', 'om-user1@example.com'],
            ['/api/organizations/{organization}/members/{organizationMember}', 'DELETE', 'user1@example.com'],
            ['/api/organizations/{organization}/members/{organizationMember}', 'DELETE', 'om-user1@example.com'],
            ['/api/organizations/{organization}/members/{organizationMember}/schedule-assignments', 'GET', 'user1@example.com'],
            ['/api/organizations/{organization}/members/{organizationMember}/schedule-assignments', 'GET', 'om-user1@example.com'],
        ];
    }
}