<?php

declare(strict_types=1);

namespace App\DTO\OrganizationMember;

use App\DTO\AbstractDTO;
use App\DTO\OrganizationMember\Trait\OrganizationMemberRoleFieldDTO;
use App\Entity\User;
use App\Validator\Constraints as CustomAssert;

class OrganizationMemberCreateDTO extends AbstractDTO 
{
    use OrganizationMemberRoleFieldDTO;

    public function __construct(
        #[CustomAssert\EntityExists(User::class)]
        public readonly int $userId, 
        string $role
    )
    {
        $this->role = $role;
    }
}