<?php 

declare(strict_types=1);

namespace App\DataFixtures\Demo\OrganizationMember\DataProvider;

use App\Enum\Organization\OrganizationRole;

class OrganizationMemberDataProvider
{
    /** @return mixed[] */
    public static function getData(): array
    {
        return [
            [
                'organization_reference' => 'organization1',
                'user_reference' => 'user1',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization2',
                'user_reference' => 'user2',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization3',
                'user_reference' => 'user3',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization4',
                'user_reference' => 'user4',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization5',
                'user_reference' => 'user5',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization6',
                'user_reference' => 'user6',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization7',
                'user_reference' => 'user7',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization8',
                'user_reference' => 'user8',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization9',
                'user_reference' => 'user9',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization10',
                'user_reference' => 'user10',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization11',
                'user_reference' => 'user11',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization12',
                'user_reference' => 'user12',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization13',
                'user_reference' => 'user13',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization14',
                'user_reference' => 'user14',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization15',
                'user_reference' => 'user15',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization16',
                'user_reference' => 'user16',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization17',
                'user_reference' => 'user17',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization18',
                'user_reference' => 'user18',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization19',
                'user_reference' => 'user19',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization20',
                'user_reference' => 'user20',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization21',
                'user_reference' => 'user21',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization22',
                'user_reference' => 'user22',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization23',
                'user_reference' => 'user23',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization24',
                'user_reference' => 'user24',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization25',
                'user_reference' => 'user25',
                'role' => OrganizationRole::ADMIN->value,
            ],
            [
                'organization_reference' => 'organization1',
                'user_reference' => 'user26',
                'role' => OrganizationRole::MEMBER->value,
            ],
            [
                'organization_reference' => 'organization5',
                'user_reference' => 'user27',
                'role' => OrganizationRole::MEMBER->value,
            ],
            [
                'organization_reference' => 'organization10',
                'user_reference' => 'user28',
                'role' => OrganizationRole::MEMBER->value,
            ],
            [
                'organization_reference' => 'organization15',
                'user_reference' => 'user29',
                'role' => OrganizationRole::MEMBER->value,
            ],
            [
                'organization_reference' => 'organization20',
                'user_reference' => 'user30',
                'role' => OrganizationRole::MEMBER->value,
            ],
            [
                'organization_reference' => 'organization25',
                'user_reference' => 'user31',
                'role' => OrganizationRole::MEMBER->value,
            ],
            [
                'organization_reference' => 'organization3',
                'user_reference' => 'user32',
                'role' => OrganizationRole::MEMBER->value,
            ],
            [
                'organization_reference' => 'organization7',
                'user_reference' => 'user33',
                'role' => OrganizationRole::MEMBER->value,
            ],
            [
                'organization_reference' => 'organization14',
                'user_reference' => 'user34',
                'role' => OrganizationRole::MEMBER->value,
            ],
            [
                'organization_reference' => 'organization21',
                'user_reference' => 'user35',
                'role' => OrganizationRole::MEMBER->value,
            ],
        ];
    }
}