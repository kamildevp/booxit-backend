<?php

declare(strict_types=1);

namespace App\DTO\ScheduleService;

use App\DTO\AbstractDTO;
use App\Validator\Constraints\Compound as Compound;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ScheduleServiceAvailabilityGetDTO extends AbstractDTO
{
    public function __construct(
        #[Compound\DateStringRequirements(true)]
        public readonly ?string $dateFrom = null,
        #[Compound\DateStringRequirements(true)]
        public readonly ?string $dateTo = null,
        #[Assert\NotBlank(allowNull: true)]
        #[Assert\Timezone]
        public readonly ?string $timezone = null,
    )
    {

    }

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        $dateFrom = $this->dateFrom ?  DateTimeImmutable::createFromFormat('Y-m-d', $this->dateFrom) : null;
        $dateTo = $this->dateTo ? DateTimeImmutable::createFromFormat('Y-m-d', $this->dateTo) : null;

        if ($dateFrom && !$dateTo) {
            $context->buildViolation('The end date must be provided when a start date is specified.')
                ->atPath('dateTo')
                ->addViolation();
            return;
        }

        if (!$dateFrom && $dateTo) {
            $context->buildViolation('The start date must be provided when an end date is specified.')
                ->atPath('dateFrom')
                ->addViolation();
            return;
        }

        if ($dateFrom && $dateTo) {
            if ($dateFrom > $dateTo) {
                $context->buildViolation('The end date cannot be earlier than the start date.')
                    ->atPath('dateTo')
                    ->addViolation();
                return;
            }

            $diff = $dateTo->diff($dateFrom);
            if ($diff->days > 30) {
                $context->buildViolation('The date range cannot exceed 31 days.')
                    ->atPath('dateTo')
                    ->addViolation();
            }
        }
    }
}