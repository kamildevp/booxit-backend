<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity\DataProvider;

use App\Enum\Weekday;
use App\Model\TimeWindow;
use App\Tests\Utils\DataProvider\BaseDataProvider;
use DateTimeImmutable;
use DateTimeZone;

class AvailabilityServiceDataProvider extends BaseDataProvider
{
    public static function getScheduleAvailabilityDataCases()
    {
        $defaultTimezone = new DateTimeZone('UTC');
        $timezone = new DateTimeZone('Europe/Warsaw');
        $startDate = (new DateTimeImmutable('monday next week', $timezone));
        $endDate = (new DateTimeImmutable('wednesday next week', $timezone));
        
        return [
            [
                [
                    new TimeWindow($startDate->setTime(23,00), $startDate->modify('+1 day')->setTime(0,0)),
                    new TimeWindow($startDate->modify('+1 day')->setTime(0,0), $startDate->modify('+1 day')->setTime(1,0)),
                    new TimeWindow($endDate->setTime(0,0), $endDate->setTime(1,0))
                ],
                $timezone,
                [
                    (new TimeWindow($startDate->setTime(23,30), $startDate->modify('+1 day')->setTime(0,0)))->setTimezone($defaultTimezone),
                    (new TimeWindow($startDate->modify('+1 day')->setTime(0,30), $startDate->modify('+1 day')->setTime(1,0)))->setTimezone($defaultTimezone),
                ],
                $startDate,
                $endDate,
                15,
                5,
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
                    new TimeWindow($startDate->setTime(23,00), $startDate->modify('+1 day')->setTime(0,0)),
                    new TimeWindow($startDate->modify('+1 day')->setTime(0,0), $startDate->modify('+1 day')->setTime(1,0)),
                    new TimeWindow($endDate->setTime(0,0), $endDate->setTime(1,0))
                ],
                $timezone,
                [
                    (new TimeWindow($startDate->setTime(23,30), $startDate->modify('+1 day')->setTime(0,0)))->setTimezone($defaultTimezone),
                    (new TimeWindow($startDate->modify('+1 day')->setTime(0,30), $startDate->modify('+1 day')->setTime(1,0)))->setTimezone($defaultTimezone),
                ],
                $startDate,
                $endDate,
                15,
                5,
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
                    new TimeWindow($startDate->setTime(23,00), $startDate->modify('+1 day')->setTime(0,0)),
                    new TimeWindow($startDate->modify('+1 day')->setTime(0,0), $startDate->modify('+1 day')->setTime(1,0)),
                    new TimeWindow($endDate->setTime(0,0), $endDate->setTime(1,0))
                ],
                $timezone,
                [
                    (new TimeWindow($startDate->setTime(23,30), $startDate->modify('+1 day')->setTime(0,0)))->setTimezone($defaultTimezone),
                    (new TimeWindow($startDate->modify('+1 day')->setTime(0,30), $startDate->modify('+1 day')->setTime(1,0)))->setTimezone($defaultTimezone),
                ],
                $startDate,
                $endDate,
                30,
                5,
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
            [
                [
                    (new TimeWindow($startDate->setTime(23,00), $startDate->modify('+1 day')->setTime(0,0)))->setTimezone(new DateTimeZone('Asia/Tokyo')),
                    (new TimeWindow($startDate->modify('+1 day')->setTime(0,0), $startDate->modify('+1 day')->setTime(1,0)))->setTimezone(new DateTimeZone('Asia/Tokyo')),
                    (new TimeWindow($endDate->setTime(0,0), $endDate->setTime(1,0)))->setTimezone(new DateTimeZone('Asia/Tokyo'))
                ],
                new DateTimeZone('Asia/Tokyo'),
                [
                    (new TimeWindow($startDate->setTime(23,30), $startDate->modify('+1 day')->setTime(0,0)))->setTimezone($defaultTimezone),
                    (new TimeWindow($startDate->modify('+1 day')->setTime(0,30), $startDate->modify('+1 day')->setTime(1,0)))->setTimezone($defaultTimezone),
                ],
                $startDate->setTimezone(new DateTimeZone('Asia/Tokyo')),
                $endDate->setTimezone(new DateTimeZone('Asia/Tokyo')),
                15,
                5,
                [
                    (new TimeWindow($startDate->setTime(23,00), $startDate->setTime(23,30)))->setTimezone(new DateTimeZone('Asia/Tokyo')),
                    (new TimeWindow($startDate->modify('+1 day')->setTime(0,0), $startDate->modify('+1 day')->setTime(0,30)))->setTimezone(new DateTimeZone('Asia/Tokyo')),
                    (new TimeWindow($endDate->setTime(0,0), $endDate->setTime(1,0)))->setTimezone(new DateTimeZone('Asia/Tokyo'))
                ],
                [1,1,1],
                [
                    $startDate->format('Y-m-d') => [],
                    $startDate->modify('+1 day')->format('Y-m-d') => [
                        '07:00',
                        '08:00'
                    ],
                    $endDate->format('Y-m-d') => [
                        '08:00',
                        '08:15',
                        '08:30'
                    ]
                ]
            ],
        ];
    }
}