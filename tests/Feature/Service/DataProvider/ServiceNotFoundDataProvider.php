<?php

declare(strict_types=1);

namespace App\Tests\Feature\Service\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ServiceNotFoundDataProvider extends BaseDataProvider 
{
    public static function dataCases()
    {
        return [
            [
                '/api/services/1000',
                'GET',
            ],
            [
                '/api/services/1000',
                'PATCH',
            ],
        ];
    }
}