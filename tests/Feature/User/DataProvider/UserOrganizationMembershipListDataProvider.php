<?php

declare(strict_types=1);

namespace App\Tests\Feature\User\DataProvider;

use App\Enum\Organization\OrganizationRole;
use App\Tests\Utils\DataProvider\ListDataProvider;
use App\Tests\Feature\Organization\DataProvider\OrganizationListDataProvider;

class UserOrganizationMembershipListDataProvider extends ListDataProvider 
{
    public static function filtersDataCases()
    {
        $organizationDataCases = OrganizationListDataProvider::filtersDataCases();
        $nestedOrganizationDataCases = array_map(
            fn($case) => array_map(
                fn($arg) => ['organization' => $arg],
                $case
            ),
            $organizationDataCases
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
        ], $nestedOrganizationDataCases);
    }

    public static function sortingDataCases()
    {
        $organizationDataCases = OrganizationListDataProvider::sortingDataCases();
        $nestedOrganizationDataCases = array_map(
            fn($case) => [
                str_starts_with($case[0], '-') ? '-organization.'.str_replace('-','',$case[0]) : "organization.$case[0]",
                array_map(fn($val) => ['organization' => $val], $case[1])
            ],
            $organizationDataCases
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
        ], $nestedOrganizationDataCases);
    }

    public static function validationDataCases()
    {
        $organizationFilterDataCases = array_filter(OrganizationListDataProvider::validationDataCases(), fn($case) => isset($case[0]['filters']));
        $nestedOrganizationDataCases = array_map(
            fn($case) => array_map(
                fn($arg) => ['filters' => ['organization' => $arg['filters']]],
                $case
            ),
            $organizationFilterDataCases
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
        ], $nestedOrganizationDataCases);
    }
}