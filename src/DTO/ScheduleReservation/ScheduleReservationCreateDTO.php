<?php

declare(strict_types=1);

namespace App\DTO\ScheduleReservation;

use App\DTO\AbstractDTO;
use App\DTO\Attribute\EntityReference;
use App\DTO\EmailConfirmation\Trait\VerificationHandlerFieldDTO;
use App\DTO\Trait\LanguagePreferenceFieldDTO;
use App\Entity\Service;
use App\Validator\Constraints as CustomAssert;
use App\Validator\Constraints\Compound as Compound;
use OpenApi\Attributes as OA;

class ScheduleReservationCreateDTO extends AbstractDTO 
{
    use VerificationHandlerFieldDTO, LanguagePreferenceFieldDTO;

    public function __construct(
        #[CustomAssert\EntityExists(Service::class, relatedTo: ['schedules' => '{route:schedule}'])]
        #[EntityReference(Service::class, 'service')]
        public readonly int $serviceId,
        #[OA\Property(format: 'email')]
        #[Compound\EmailRequirements]
        public readonly string $email,
        #[OA\Property(example:'+48213721372')]
        #[Compound\PhoneNumberRequirements]
        public readonly string $phoneNumber,
        #[OA\Property(format: 'date-time')]
        #[Compound\DateTimeStringRequirements]
        public readonly string $startDateTime,
        string $verificationHandler,
        string $languagePreference,
    )
    {
        $this->verificationHandler = $verificationHandler;
        $this->languagePreference = $languagePreference;
    }
}