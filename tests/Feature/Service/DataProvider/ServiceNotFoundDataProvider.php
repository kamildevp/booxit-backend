<?php

declare(strict_types=1);

namespace App\Tests\Feature\Service\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ServiceNotFoundDataProvider extends BaseDataProvider 
{
    public static function dataCases()
    {
        return [
            ['/api/organizations/1000/services/1000', 'GET', 'Service not found'],
            ['/api/organizations/{organization}/services/1000', 'GET', 'Service not found'],
            ['/api/organizations/1000/services/1000', 'PATCH', 'Organization not found'],
            ['/api/organizations/{organization}/services/1000', 'PATCH', 'Service not found'],
            ['/api/organizations/1000/services/1000', 'DELETE', 'Organization not found'],   
            ['/api/organizations/{organization}/services/1000', 'DELETE', 'Service not found'],
        ];
    }
}