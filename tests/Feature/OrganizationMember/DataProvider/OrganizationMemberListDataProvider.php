<?php

declare(strict_types=1);

namespace App\Tests\Feature\OrganizationMember\DataProvider;

use App\Enum\Organization\OrganizationRole;
use App\Tests\Feature\Global\DataProvider\ListDataProvider;
use App\Tests\Feature\User\DataProvider\UserListDataProvider;

class OrganizationMemberListDataProvider extends ListDataProvider 
{
    public static function filtersDataCases()
    {
        $userDataCases = UserListDataProvider::filtersDataCases();
        $nestedUserDataCases = array_map(
            fn($case) => array_map(
                fn($arg) => ['app_user' => $arg],
                $case
            ),
            $userDataCases
        );

        return array_merge([
            [
                [
                    'role' => OrganizationRole::ADMIN->value
                ],
                [
                    'role' => OrganizationRole::ADMIN->value
                ],
            ],
        ], $nestedUserDataCases);
    }

    public static function sortingDataCases()
    {
        $userDataCases = UserListDataProvider::sortingDataCases();
        $nestedUserDataCases = array_map(
            fn($case) => [
                str_starts_with($case[0], '-') ? '-app_user.'.str_replace('-','',$case[0]) : "app_user.$case[0]",
                array_map(fn($val) => ['app_user' => $val], $case[1])
            ],
            $userDataCases
        );

        return array_merge([
            [
                'role',
                parent::getSortedColumnValueSequence('role', 'organization_role')
            ],
            [
                '-role',
                parent::getSortedColumnValueSequence('role', 'organization_role', 'desc')
            ],
        ], $nestedUserDataCases);
    }

    public static function validationDataCases()
    {
        $userFilterDataCases = array_filter(UserListDataProvider::validationDataCases(), fn($case) => isset($case[0]['filters']));
        $nestedUserDataCases = array_map(
            fn($case) => array_map(
                fn($arg) => ['filters' => ['app_user' => $arg['filters']]],
                $case
            ),
            $userFilterDataCases
        );

        return array_merge([
            [
                [
                    'filters' => [
                        'role' => 'a',
                    ]
                ],
                [
                    'filters' => [
                        'role' => [
                            'Parameter must be one of valid roles: '.implode(', ', array_map(fn($val) => '"'.$val.'"', OrganizationRole::values())),
                        ],
                    ]
                ]
            ],
        ], $nestedUserDataCases);
    }
}