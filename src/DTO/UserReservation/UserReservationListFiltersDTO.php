<?php

declare(strict_types=1);

namespace App\DTO\UserReservation;

use App\DTO\ListFiltersDTO;
use App\DTO\Trait\TimestampsFiltersFieldsDTO;
use App\Enum\Reservation\ReservationStatus;
use App\Validator\Constraints\Compound as Compound;
use OpenApi\Attributes as OA;

class UserReservationListFiltersDTO extends ListFiltersDTO 
{
    use TimestampsFiltersFieldsDTO;

    public function __construct(
        #[OA\Property(type: 'array', items: new OA\Items(type: 'integer'))]
        #[Compound\FieldInSetFilterRequirements('digit')] public readonly ?array $organizationId = null,
        #[OA\Property(type: 'array', items: new OA\Items(type: 'integer'))]
        #[Compound\FieldInSetFilterRequirements('digit')] public readonly ?array $scheduleId = null,
        #[OA\Property(type: 'array', items: new OA\Items(type: 'integer'))]
        #[Compound\FieldInSetFilterRequirements('digit')] public readonly ?array $serviceId = null,
        #[Compound\DateTimeStringRequirements(true)] public readonly ?string $startDateTimeFrom = null,
        #[Compound\DateTimeStringRequirements(true)] public readonly ?string $startDateTimeTo = null,
        #[Compound\DateTimeStringRequirements(true)] public readonly ?string $endDateTimeFrom = null,
        #[Compound\DateTimeStringRequirements(true)] public readonly ?string $endDateTimeTo = null,
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
        #[Compound\EnumSetRequirements(ReservationStatus::class)] public readonly ?array $status = null,
        #[Compound\DecimalRequirements(true)] public readonly ?string $estimatedPriceFrom = null,
        #[Compound\DecimalRequirements(true)] public readonly ?string $estimatedPriceTo = null,
        #[Compound\ContainsFilterRequirements] public readonly ?string $reference = null,
        ?string $createdFrom = null,
        ?string $createdTo = null,
        ?string $updatedFrom = null,
        ?string $updatedTo = null,
    )
    {
        $this->createdFrom = $createdFrom;
        $this->createdTo = $createdTo;
        $this->updatedFrom = $updatedFrom;
        $this->updatedTo = $updatedTo;
    }
}