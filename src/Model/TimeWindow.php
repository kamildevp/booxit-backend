<?php

declare(strict_types=1);

namespace App\Model;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class TimeWindow
{
    private DateTimeImmutable $startTime;
    private DateTimeImmutable $endTime;

    public function __construct(
        DateTimeInterface $startTime,
        DateTimeInterface $endTime
    )
    {
        $this->startTime = DateTimeImmutable::createFromInterface($startTime);
        $this->endTime = DateTimeImmutable::createFromInterface($endTime);
    }

    public static function createFromDateAndTime(
        DateTimeInterface|string $startDate, 
        DateTimeInterface|string $startTime,
        DateTimeInterface|string $endDate, 
        DateTimeInterface|string $endTime,
        ?DateTimeZone $timezone = null
    ): self|false
    {
        $startDateString = $startDate instanceof DateTimeInterface ? $startDate->format('Y-m-d') : $startDate;
        $startTimeString = $startTime instanceof DateTimeInterface ? $startTime->format('H:i') : $startTime;
        $endDateString = $endDate instanceof DateTimeInterface ? $endDate->format('Y-m-d') : $endDate;
        $endTimeString = $endTime instanceof DateTimeInterface ? $endTime->format('H:i') : $endTime;

        $startDateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i', "$startDateString $startTimeString", $timezone);
        $endDateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i', "$endDateString $endTimeString", $timezone);
        if($startDateTime === false || $endDateTime === false){
            return false;
        }

        $endDateTime = $endDateTime <= $startDateTime ? $endDateTime->modify('+1 day') : $endDateTime;

        return new self($startDateTime, $endDateTime);
    }

    #[Context([DateTimeNormalizer::FORMAT_KEY => 'H:i'])]
    public function getStartTime(): DateTimeImmutable
    {
        return $this->startTime;
    }

    #[Context([DateTimeNormalizer::FORMAT_KEY => 'H:i'])]
    public function getEndTime(): DateTimeImmutable
    {
        return $this->endTime;
    }

    #[Ignore]
    public function getLength(): DateInterval
    {
        return $this->startTime->diff($this->endTime);
    }

    public function setTimezone(DateTimeZone $timezone): self
    {
        $this->startTime = $this->startTime->setTimezone($timezone);
        $this->endTime = $this->endTime->setTimezone($timezone);

        return $this;
    }
}