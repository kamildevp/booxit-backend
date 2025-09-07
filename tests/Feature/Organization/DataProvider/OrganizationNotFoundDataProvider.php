<?php

declare(strict_types=1);

namespace App\Tests\Feature\Organization\DataProvider;

use App\Tests\Feature\Global\DataProvider\BaseDataProvider;

class OrganizationNotFoundDataProvider extends BaseDataProvider 
{
    public static function dataCases()
    {
        return [
            [
                '/api/organization/1000',
                'GET',
            ],
            [
                '/api/organization/1000',
                'PATCH',
            ],
            [
                '/api/organization/1000/banner',
                'PUT',
            ],
            [
                '/api/organization/1000/banner',
                'GET',
            ],
        ];
    }
}