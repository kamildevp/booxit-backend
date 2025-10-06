<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleAssignment\DataProvider;

use App\Enum\Schedule\ScheduleAccessType;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleAssignmentCreateDataProvider extends BaseDataProvider 
{
    
    public static function validDataCases()
    {
        return [
            [
                [
                    'access_type' => ScheduleAccessType::READ->value,
                ],
                [
                    'access_type' => ScheduleAccessType::READ->value,
                ],
            ],
            [
                [
                    'access_type' => ScheduleAccessType::WRITE->value,
                ],
                [
                    'access_type' => ScheduleAccessType::WRITE->value,
                ],
            ],
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'organization_member_id' => 0,
                    'access_type' => 'a',
                ],
                [
                    'organization_member_id' => [
                        'OrganizationMember does not exist',
                    ],
                    'access_type' => [
                        'Parameter must be one of valid access types: '.implode(', ', array_map(fn($val) => '"'.$val.'"', ScheduleAccessType::values())),
                    ],
                ]
            ],
        ];
    }
}