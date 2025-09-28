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
                '/api/organization/1000/member',
                'GET',
                'Organization not found'
            ],
            [
                '/api/organization/1000/member',
                'POST',
                'Organization not found'
            ],
            [
                '/api/organization-member/1000',
                'GET',
                'OrganizationMember not found'
            ],
            [
                '/api/organization-member/1000',
                'DELETE',
                'OrganizationMember not found'
            ],
            [
                '/api/organization-member/1000',
                'PATCH',
                'OrganizationMember not found'
            ],
        ];
    }
}