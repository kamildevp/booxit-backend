<?php

declare(strict_types=1);

namespace App\DTO\Reservation;

use App\DTO\AbstractDTO;
use App\DTO\Attribute\EntityReference;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Enum\Reservation\ReservationStatus;
use App\Validator\Constraints as CustomAssert;
use App\Validator\Constraints\Compound as Compound;
use App\Validator\Constraints\Compound\DateTimeStringRequirements;
use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ReservationCreateCustomDTO extends AbstractDTO 
{
    public function __construct(
        #[CustomAssert\EntityExists(Schedule::class, commonRelations: ['organization' => ['reservations', '{route:reservation}']])]
        #[EntityReference(Schedule::class, 'schedule')]
        public readonly int $scheduleId,
        #[CustomAssert\EntityExists(Service::class, relatedTo: ['schedules' => '{body:schedule_id}'])]
        #[EntityReference(Service::class, 'service')]
        public readonly int $serviceId,
        #[OA\Property(format: 'email')]
        #[Compound\EmailRequirements]
        public readonly string $email,
        #[OA\Property(example:'+48213721372')]
        #[Compound\PhoneNumberRequirements]
        public readonly string $phoneNumber,
        #[OA\Property(example: '25.50')]
        #[Compound\DecimalRequirements]
        public readonly string $estimatedPrice,
        #[OA\Property(format: 'date-time')]
        #[Compound\DateTimeStringRequirements]
        public readonly string $startDateTime,
        #[OA\Property(format: 'date-time')]
        #[Compound\DateTimeStringRequirements]
        public readonly string $endDateTime,
        #[Assert\Choice(callback: [ReservationStatus::class, 'values'], message: 'Parameter must be one of valid statuses: {{ choices }}')]
        public readonly string $status,
    )
    {

    }

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        $startDateTime = DateTimeImmutable::createFromFormat(DateTimeStringRequirements::FORMAT, $this->startDateTime);
        $endDateTime = DateTimeImmutable::createFromFormat(DateTimeStringRequirements::FORMAT, $this->endDateTime);

        if ($startDateTime && $endDateTime && $startDateTime >= $endDateTime) {
            $context->buildViolation('End date time must be later than the start date time.')
                ->atPath('endDateTime')
                ->addViolation();
        }
    }
}