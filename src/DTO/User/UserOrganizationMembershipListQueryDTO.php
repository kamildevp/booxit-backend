<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\DTO\AbstractDTO;
use App\DTO\ListQueryDTOInterface;
use App\DTO\Organization\OrganizationListQueryDTO;
use App\DTO\Trait\OrderFieldsDTO;
use App\DTO\Trait\PaginationFieldsDTO;
use App\Repository\OrganizationMemberRepository;
use Nelmio\ApiDocBundle\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

class UserOrganizationMembershipListQueryDTO extends AbstractDTO implements ListQueryDTOInterface
{
    use PaginationFieldsDTO, OrderFieldsDTO;

    public function __construct(
        #[Assert\Valid]
        public readonly ?UserOrganizationMembershipListFiltersDTO $filters = null,
        int $page = 1,
        int $perPage = OrganizationMemberRepository::DEFAULT_ENTRIES_PER_PAGE,
        ?string $order = null
    )
    {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->order = $order;
    }

    #[Ignore]
    public static function getOrderableColumns(): array
    {
        $organizationOrderableColumns = array_map(fn($column) => "organization.$column", OrganizationListQueryDTO::getOrderableColumns());
        return array_merge($organizationOrderableColumns, ['role']);
    }
}