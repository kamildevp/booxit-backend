<?php

declare(strict_types=1);

namespace App\Tests\Feature\Organization\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class OrganizationNotFoundDataProvider extends BaseDataProvider 
{
    public static function dataCases()
    {
        return [
            [
                '/api/organizations/1000',
                'GET',
            ],
            [
                '/api/organizations/1000',
                'PATCH',
            ],
            [
                '/api/organizations/1000/banner',
                'PUT',
            ],
            [
                '/api/organizations/1000/banner',
                'GET',
            ],
        ];
    }
}