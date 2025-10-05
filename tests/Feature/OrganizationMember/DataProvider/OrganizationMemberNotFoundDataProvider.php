<?php

declare(strict_types=1);

namespace App\Tests\Feature\OrganizationMember\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class OrganizationMemberNotFoundDataProvider extends BaseDataProvider 
{
    public static function dataCases()
    {
        return [
            [
                '/api/organizations/1000/members',
                'GET',
                'Organization not found'
            ],
            [
                '/api/organizations/1000/members',
                'POST',
                'Organization not found'
            ],
            [
                '/api/organizations/{organization}/members/1000',
                'GET',
                'OrganizationMember not found'
            ],
            [
                '/api/organizations/1000/members/1000',
                'DELETE',
                'Organization not found'
            ],
            [
                '/api/organizations/{organization}/members/1000',
                'DELETE',
                'OrganizationMember not found'
            ],
            [
                '/api/organizations/1000/members/1000',
                'PATCH',
                'Organization not found'
            ],
            [
                '/api/organizations/{organization}/members/1000',
                'PATCH',
                'OrganizationMember not found'
            ],
        ];
    }
}