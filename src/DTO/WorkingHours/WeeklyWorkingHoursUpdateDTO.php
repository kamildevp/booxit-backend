<?php

declare(strict_types=1);

namespace App\DTO\WorkingHours;

use App\DTO\AbstractDTO;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use OpenApi\Attributes as OA;

#[CustomAssert\WeeklyWorkingHours]
class WeeklyWorkingHoursUpdateDTO extends AbstractDTO 
{
    public function __construct(
        #[OA\Property(example: [['start_time' => '09:00', 'end_time' => '17:00']])]
        /** @var TimeWindowDTO[] */
        #[Assert\Valid]
        public readonly array $monday,

        #[OA\Property(example: [['start_time' => '09:00', 'end_time' => '17:00']])]
        /** @var TimeWindowDTO[] */
        #[Assert\Valid]
        public readonly array $tuesday,

        #[OA\Property(example: [['start_time' => '09:00', 'end_time' => '17:00']])]
        /** @var TimeWindowDTO[] */
        #[Assert\Valid]
        public readonly array $wednesday,

        #[OA\Property(example: [['start_time' => '09:00', 'end_time' => '17:00']])]
        /** @var TimeWindowDTO[] */
        #[Assert\Valid]
        public readonly array $thursday,
        /** @var TimeWindowDTO[] */

        #[OA\Property(example: [['start_time' => '09:00', 'end_time' => '17:00']])]
        #[Assert\Valid]
        public readonly array $friday,

        #[OA\Property(example: [])]
        /** @var TimeWindowDTO[] */
        #[Assert\Valid]
        public readonly array $saturday,
        
        #[OA\Property(example: [])]
        /** @var TimeWindowDTO[] */
        #[Assert\Valid]
        public readonly array $sunday,
    )
    {

    }
}