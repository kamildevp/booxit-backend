<?php

declare(strict_types=1);

namespace App\DTO\OrganizationMember;

use App\DTO\AbstractDTO;
use App\DTO\ListQueryDTOInterface;
use App\DTO\Trait\OrderFieldsDTO;
use App\DTO\Trait\PaginationFieldsDTO;
use App\DTO\User\UserListQueryDTO;
use App\Repository\OrganizationMemberRepository;
use Nelmio\ApiDocBundle\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

class OrganizationMemberListQueryDTO extends AbstractDTO implements ListQueryDTOInterface
{
    use PaginationFieldsDTO, OrderFieldsDTO;

    public function __construct(
        #[Assert\Valid]
        public readonly OrganizationMemberListFiltersDTO $filters = new OrganizationMemberListFiltersDTO(),
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
        $userOrderableColumns = array_map(fn($column) => "app_user.$column", UserListQueryDTO::getOrderableColumns());
        return array_merge($userOrderableColumns, ['role']);
    }
}