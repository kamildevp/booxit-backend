<?php

namespace App\Service\GetterHelper\CustomFormat;

class DateIntervalFormat implements CustomFormatInterface
{
    /** @param \DateInterval $property */
    public function format($property)
    {
        return $property->format('P%YY%MM%DDT%HH%IM%SS');
    }
}