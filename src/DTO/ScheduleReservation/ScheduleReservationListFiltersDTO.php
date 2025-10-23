<?php

declare(strict_types=1);

namespace App\DTO\ScheduleReservation;

use App\DTO\ListFiltersDTO;
use App\DTO\Trait\TimestampsFiltersFieldsDTO;
use App\Enum\Reservation\ReservationStatus;
use App\Enum\Reservation\ReservationType;
use App\Validator\Constraints\Compound as Compound;
use OpenApi\Attributes as OA;

class ScheduleReservationListFiltersDTO extends ListFiltersDTO 
{
    use TimestampsFiltersFieldsDTO;

    public function __construct(
        #[OA\Property(type: 'array', items: new OA\Items(type: 'integer'))]
        #[Compound\FieldInSetFilterRequirements('digit')] public readonly ?array $reservedById = null,
        #[OA\Property(type: 'array', items: new OA\Items(type: 'integer'))]
        #[Compound\FieldInSetFilterRequirements('digit')] public readonly ?array $serviceId = null,
        #[Compound\DateTimeStringRequirements(true)] public readonly ?string $startDateTimeFrom = null,
        #[Compound\DateTimeStringRequirements(true)] public readonly ?string $startDateTimeTo = null,
        #[Compound\DateTimeStringRequirements(true)] public readonly ?string $endDateTimeFrom = null,
        #[Compound\DateTimeStringRequirements(true)] public readonly ?string $endDateTimeTo = null,
        #[Compound\DateTimeStringRequirements(true)] public readonly ?string $expiryDateFrom = null,
        #[Compound\DateTimeStringRequirements(true)] public readonly ?string $expiryDateTo = null,
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
        #[Compound\EnumSetRequirements(ReservationType::class)] public readonly ?array $type = null,
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
        #[Compound\EnumSetRequirements(ReservationStatus::class)] public readonly ?array $status = null,
        #[Compound\DecimalRequirements(true)] public readonly ?string $estimatedPriceFrom = null,
        #[Compound\DecimalRequirements(true)] public readonly ?string $estimatedPriceTo = null,
        #[Compound\ContainsFilterRequirements] public readonly ?string $reference = null,
        #[Compound\ContainsFilterRequirements] public readonly ?string $email = null,
        #[Compound\ContainsFilterRequirements] public readonly ?string $phoneNumber = null,
        public readonly ?bool $verified = null,
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