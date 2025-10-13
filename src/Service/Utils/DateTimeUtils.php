<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Model\TimeWindow;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

class DateTimeUtils
{
    /** @param TimeWindow[] $collection1 */
    /** @param TimeWindow[] $collection2 */
    /** @return TimeWindow[] */
    public function timeWindowCollectionDiff(array $collection1, array $collection2): array
    {
        if(empty($collection2)){
            return $collection1;
        }

        $diff = [];
        foreach($collection1 as $element1){
            $currentCollection = [$element1];
            foreach($collection2 as $element2){
                if(empty($currentCollection)){
                    break;
                }
                
                if(count($currentCollection) > 1){
                    $currentCollection = $this->timeWindowCollectionDiff($currentCollection, [$element2]);
                }
                else{
                    $currentCollection = $this->subtractTimeWindow($currentCollection[0], $element2);
                }

            }
            $diff = array_merge($diff, $currentCollection);
        }

        return array_values($diff);
    }

    /** @return TimeWindow[] */
    public function subtractTimeWindow(TimeWindow $timeWindow1, TimeWindow $timeWindow2): array
    {
        $result = [];
        $startTime1 = $timeWindow1->getStartTime();
        $endTime1 = $timeWindow1->getEndTime();
        $startTime2 = $timeWindow2->getStartTime();
        $endTime2 = $timeWindow2->getEndTime();

        switch(true){
            case $endTime1 <= $startTime2 || $startTime1 >= $endTime2:
                $result[] = new TimeWindow($startTime1, $endTime1);
                break;
            case $startTime1 >= $startTime2 && $endTime1 <= $endTime2:
                break;
            case $startTime1 < $startTime2 && $endTime1 > $endTime2:
                $result[] = new TimeWindow($startTime1, $startTime2);
                $result[] = new TimeWindow($endTime2, $endTime1);
                break; 
            case $startTime1 >= $startTime2:
                $result[] = new TimeWindow($endTime2, $endTime1);
                break;
            case $endTime1 <= $endTime2:
                $result[] = new TimeWindow($startTime1, $startTime2);
                break;   
        }

        return $result;
    }

    public function compareDateIntervals(DateInterval $dateInterval1, DateInterval $dateInterval2): int
    {
        $dateTime = new DateTimeImmutable();
        $dateTime1 = $dateTime->add($dateInterval1);
        $dateTime2 = $dateTime->add($dateInterval2);

        if($dateTime1 == $dateTime2){
            return 0;
        }

        return $dateTime1 > $dateTime2 ? 1 : -1;
    }

    public function resolveDateTimeImmutableWithDefault(DateTimeInterface|string|null $date, DateTimeInterface $default, string $format = 'Y-m-d'): DateTimeImmutable
    {
        if($date instanceof DateTimeImmutable){
            return $date;
        }

        if($date instanceof DateTimeInterface){
            return DateTimeImmutable::createFromInterface($date);
        }

        return $date ? DateTimeImmutable::createFromFormat($format, $date) : DateTimeImmutable::createFromInterface($default);
    }
}