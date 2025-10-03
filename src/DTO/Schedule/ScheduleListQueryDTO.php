<?php

declare(strict_types=1);

namespace App\DTO\Schedule;

use App\DTO\AbstractDTO;
use App\DTO\ListQueryDTOInterface;
use App\DTO\Trait\OrderFieldsDTO;
use App\DTO\Trait\PaginationFieldsDTO;
use App\Enum\TimestampsColumns;
use App\Repository\ScheduleRepository;
use Nelmio\ApiDocBundle\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

class ScheduleListQueryDTO extends AbstractDTO implements ListQueryDTOInterface
{
    use PaginationFieldsDTO, OrderFieldsDTO;

    public function __construct(
        #[Assert\Valid]
        public readonly ?ScheduleListFiltersDTO $filters = null,
        int $page = 1,
        int $perPage = ScheduleRepository::DEFAULT_ENTRIES_PER_PAGE,
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
        return array_merge(TimestampsColumns::values(), ['name']);
    }
}