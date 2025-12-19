<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity\DataProvider;

use App\Enum\Weekday;
use App\Model\TimeWindow;
use App\Tests\Utils\DataProvider\BaseDataProvider;
use DateTimeImmutable;

class AvailabilityServiceDataProvider extends BaseDataProvider
{
    public static function getScheduleAvailabilityDataCases()
    {
        $startDate = (new DateTimeImmutable('monday next week'))->setTime(0,0);
        $endDate = (new DateTimeImmutable('wednesday next week'))->setTime(23,59);
        $weekdayTimeWindows = [new TimeWindow(new DateTimeImmutable('00:00'), new DateTimeImmutable('01:00'))];
        $customTimeWindows = [new TimeWindow(new DateTimeImmutable('23:00'), new DateTimeImmutable('00:00'))];
        
        return [
            [
                [
                    Weekday::MONDAY->value => $weekdayTimeWindows,
                    Weekday::TUESDAY->value => $weekdayTimeWindows,
                    Weekday::WEDNESDAY->value => $weekdayTimeWindows,
                ],
                [
                    $startDate->format('Y-m-d') => ['time_windows' => $customTimeWindows, 'timezone' => 'UTC']
                ],
                [
                    new TimeWindow($startDate->setTime(23,30), $startDate->modify('+1 day')->setTime(0,0)),
                    new TimeWindow($startDate->modify('+1 day')->setTime(0,30), $startDate->modify('+1 day')->setTime(1,0))
                ],
                $startDate,
                $endDate,
                15,
                5,
                [
                    new TimeWindow($startDate->setTime(23,00), $startDate->modify('+1 day')->setTime(0,0)),
                    new TimeWindow($startDate->modify('+1 day')->setTime(0,0), $startDate->modify('+1 day')->setTime(1,0)),
                    new TimeWindow($endDate->setTime(0,0), $endDate->setTime(1,0))
                ],
                [
                    new TimeWindow($startDate->setTime(23,00), $startDate->setTime(23,30)),
                    new TimeWindow($startDate->modify('+1 day')->setTime(0,0), $startDate->modify('+1 day')->setTime(0,30)),
                    new TimeWindow($endDate->setTime(0,0), $endDate->setTime(1,0))
                ],
                [1,1,1],
                [
                    $startDate->format('Y-m-d') => [
                        '23:00'
                    ],
                    $startDate->modify('+1 day')->format('Y-m-d') => [
                        '00:00'
                    ],
                    $endDate->format('Y-m-d') => [
                        '00:00',
                        '00:15',
                        '00:30'
                    ]
                ]
            ],
            [
                [
                    Weekday::MONDAY->value => $weekdayTimeWindows,
                    Weekday::TUESDAY->value => $weekdayTimeWindows,
                    Weekday::WEDNESDAY->value => $weekdayTimeWindows,
                ],
                [
                    $startDate->format('Y-m-d') => ['time_windows' => $customTimeWindows, 'timezone' => 'UTC']
                ],
                [
                    new TimeWindow($startDate->setTime(23,30), $startDate->modify('+1 day')->setTime(0,0)),
                    new TimeWindow($startDate->modify('+1 day')->setTime(0,30), $startDate->modify('+1 day')->setTime(1,0))
                ],
                $startDate,
                $endDate,
                15,
                5,
                [
                    new TimeWindow($startDate->setTime(23,00), $startDate->modify('+1 day')->setTime(0,0)),
                    new TimeWindow($startDate->modify('+1 day')->setTime(0,0), $startDate->modify('+1 day')->setTime(1,0)),
                    new TimeWindow($endDate->setTime(0,0), $endDate->setTime(1,0))
                ],
                [
                    new TimeWindow($startDate->setTime(23,00), $startDate->setTime(23,30)),
                    new TimeWindow($startDate->modify('+1 day')->setTime(0,0), $startDate->modify('+1 day')->setTime(0,30)),
                    new TimeWindow($endDate->setTime(0,0), $endDate->setTime(1,0))
                ],
                [1,-1,1],
                [
                    $startDate->format('Y-m-d') => [
                        '23:00'
                    ],
                    $startDate->modify('+1 day')->format('Y-m-d') => [],
                    $endDate->format('Y-m-d') => [
                        '00:00',
                        '00:15',
                        '00:30'
                    ]
                ]
            ],
            [
                [
                    Weekday::MONDAY->value => $weekdayTimeWindows,
                    Weekday::TUESDAY->value => $weekdayTimeWindows,
                    Weekday::WEDNESDAY->value => $weekdayTimeWindows,
                ],
                [
                    $startDate->format('Y-m-d') => ['time_windows' => $customTimeWindows, 'timezone' => 'UTC']
                ],
                [
                    new TimeWindow($startDate->setTime(23,30), $startDate->modify('+1 day')->setTime(0,0)),
                    new TimeWindow($startDate->modify('+1 day')->setTime(0,30), $startDate->modify('+1 day')->setTime(1,0))
                ],
                $startDate,
                $endDate,
                30,
                5,
                [
                    new TimeWindow($startDate->setTime(23,00), $startDate->modify('+1 day')->setTime(0,0)),
                    new TimeWindow($startDate->modify('+1 day')->setTime(0,0), $startDate->modify('+1 day')->setTime(1,0)),
                    new TimeWindow($endDate->setTime(0,0), $endDate->setTime(1,0))
                ],
                [
                    new TimeWindow($startDate->setTime(23,00), $startDate->setTime(23,30)),
                    new TimeWindow($startDate->modify('+1 day')->setTime(0,0), $startDate->modify('+1 day')->setTime(0,30)),
                    new TimeWindow($endDate->setTime(0,0), $endDate->setTime(1,0))
                ],
                [1,-1,1],
                [
                    $startDate->format('Y-m-d') => [
                        '23:00'
                    ],
                    $startDate->modify('+1 day')->format('Y-m-d') => [],
                    $endDate->format('Y-m-d') => [
                        '00:00',
                        '00:30'
                    ]
                ]
            ],
        ];
    }
}