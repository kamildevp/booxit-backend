<?php

declare(strict_types=1);

namespace App\DTO\WorkingHours;

use App\DTO\AbstractDTO;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use App\Validator\Constraints\Compound as Compound;
use OpenApi\Attributes as OA;

class DateWorkingHoursDTO extends AbstractDTO 
{
    public function __construct(
        #[OA\Property(format: 'date')]
        #[Compound\DateStringRequirements]
        public string $date,

        #[OA\Property(example: [['start_time' => '09:00', 'end_time' => '12:00']])]
        /** @var TimeWindowDTO[] */
        #[Assert\Valid]
        #[CustomAssert\TimeWindowCollection]
        public readonly array $timeWindows,
    )
    {

    }
}