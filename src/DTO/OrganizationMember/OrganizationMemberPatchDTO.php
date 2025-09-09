<?php

declare(strict_types=1);

namespace App\DTO\OrganizationMember;

use App\DTO\AbstractDTO;
use App\DTO\OrganizationMember\Trait\OrganizationMemberRoleFieldDTO;

class OrganizationMemberPatchDTO extends AbstractDTO 
{
    use OrganizationMemberRoleFieldDTO;

    public function __construct(
        string $role
    )
    {
        $this->role = $role;
    }
}