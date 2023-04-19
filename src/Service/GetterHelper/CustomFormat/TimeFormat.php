<?php

namespace App\Service\GetterHelper\CustomFormat;

use DateTimeInterface;

class TimeFormat implements CustomFormatInterface
{
    /** @param \DateTimeInterface $property */
    public function format($property)
    {
        return $property->format('H:i:s');
    }
}