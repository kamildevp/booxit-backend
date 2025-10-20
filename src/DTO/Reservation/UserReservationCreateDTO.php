<?php

declare(strict_types=1);

namespace App\DTO\Reservation;

use App\DTO\AbstractDTO;
use App\DTO\Attribute\EntityReference;
use App\DTO\EmailConfirmation\Trait\VerificationHandlerFieldDTO;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Validator\Constraints as CustomAssert;
use App\Validator\Constraints\Compound as Compound;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use OpenApi\Attributes as OA;

class UserReservationCreateDTO extends AbstractDTO 
{
    use VerificationHandlerFieldDTO;

    public function __construct(
        #[CustomAssert\EntityExists(Schedule::class)]
        #[EntityReference(Schedule::class, 'schedule')]
        public readonly int $scheduleId,
        #[CustomAssert\EntityExists(Service::class, relatedTo: ['schedules' => '{body:schedule_id}'])]
        #[EntityReference(Service::class, 'service')]
        public readonly int $serviceId,
        #[OA\Property(example:'+48213721372')]
        #[PhoneNumber]
        public readonly string $phoneNumber,
        #[OA\Property(format: 'date-time')]
        #[Compound\DateTimeStringRequirements]
        public readonly string $startDateTime,
        string $verificationHandler
    )
    {
        $this->verificationHandler = $verificationHandler;
    }
}