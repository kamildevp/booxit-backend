<?php

declare(strict_types=1);

namespace App\DTO\WorkingHours;

use App\DTO\AbstractDTO;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\Compound as Compound;
use App\Validator\Constraints as CustomAssert;
use OpenApi\Attributes as OA;

#[CustomAssert\CustomWorkingHours]
class CustomWorkingHoursUpdateDTO extends AbstractDTO 
{
    public function __construct(
        #[OA\Property(format: 'date')]
        #[Compound\DateStringRequirements]
        public string $date,

        #[OA\Property(example: [['start_time' => '09:00', 'end_time' => '12:00']])]
        #[Assert\Valid]
        /** @var TimeWindowDTO[] */
        public readonly array $timeWindows,

        #[OA\Property(example: 'Europe/Warsaw')]
        #[Assert\NotBlank]
        #[Assert\Timezone]
        public readonly string $timezone,
    )
    {

    }
}