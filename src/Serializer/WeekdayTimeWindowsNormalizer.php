<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\WeekdayTimeWindow;
use App\Enum\Weekday;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Doctrine\Common\Collections\Collection;

class WeekdayTimeWindowsNormalizer implements NormalizerInterface
{
    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Collection && $data->count() > 0 && $data->first() instanceof WeekdayTimeWindow;
    }

    public function normalize($collection, ?string $format = null, array $context = []): array
    {
        $result = array_fill_keys(Weekday::values(), []);
        /** @var WeekdayTimeWindow $weekdayTimeWindow */
        foreach($collection as $weekdayTimeWindow){
            $result[$weekdayTimeWindow->getWeekday()][] = [
                'start_time' => $weekdayTimeWindow->getStartTime()->format('H:i'),
                'end_time' => $weekdayTimeWindow->getEndTime()->format('H:i'),
            ];
        }

        return $result;
    }
}
