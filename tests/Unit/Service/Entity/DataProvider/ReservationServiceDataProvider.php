<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ReservationServiceDataProvider extends BaseDataProvider
{
    public static function cancelOrganizationReservationDataCases()
    {
        return [
            [true],
            [false],
        ];
    }
}