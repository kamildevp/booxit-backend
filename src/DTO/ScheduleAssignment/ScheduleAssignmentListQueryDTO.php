<?php

declare(strict_types=1);

namespace App\DTO\ScheduleAssignment;

use App\DTO\AbstractDTO;
use App\DTO\ListQueryDTOInterface;
use App\DTO\OrganizationMember\OrganizationMemberListQueryDTO;
use App\DTO\Trait\OrderFieldsDTO;
use App\DTO\Trait\PaginationFieldsDTO;
use App\Repository\ScheduleAssignmentRepository;
use Nelmio\ApiDocBundle\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

class ScheduleAssignmentListQueryDTO extends AbstractDTO implements ListQueryDTOInterface
{
    use PaginationFieldsDTO, OrderFieldsDTO;

    public function __construct(
        #[Assert\Valid]
        public readonly ScheduleAssignmentListFiltersDTO $filters = new ScheduleAssignmentListFiltersDTO(),
        int $page = 1,
        int $perPage = ScheduleAssignmentRepository::DEFAULT_ENTRIES_PER_PAGE,
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
        return array_merge(
            array_map(fn($orderColumn) => "organization_member.$orderColumn", OrganizationMemberListQueryDTO::getOrderableColumns()),
            ['access_type']
        );
    }
}