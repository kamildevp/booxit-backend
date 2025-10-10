<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\CustomTimeWindow;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Doctrine\Common\Collections\Collection;

class CustomWorkingHoursNormalizer implements NormalizerInterface
{
    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return 
            $data instanceof Collection && $data->first() instanceof CustomTimeWindow || 
            is_array($data) && reset($data) instanceof CustomTimeWindow;
    }

    public function normalize($collection, ?string $format = null, array $context = []): array
    {
        $result = [];
        /** @var CustomTimeWindow $customTimeWindow */
        foreach($collection as $customTimeWindow){
            $date = $customTimeWindow->getDate()->format('Y-m-d');
            $result[$date][] = [
                'start_time' => $customTimeWindow->getStartTime()->format('H:i'),
                'end_time' => $customTimeWindow->getEndTime()->format('H:i'),
            ];
        }

        return $result;
    }
}
