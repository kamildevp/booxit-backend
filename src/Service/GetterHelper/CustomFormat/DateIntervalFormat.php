<?php

namespace App\Service\GetterHelper\CustomFormat;

class DateIntervalFormat implements CustomFormatInterface
{
    /** @param \DateInterval $property */
    public function format($property)
    {
        return $property->format('PT%HH%IM');
    }
}