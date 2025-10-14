<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Utils\DataProvider;

use App\Model\TimeWindow;
use App\Tests\Utils\DataProvider\BaseDataProvider;
use DateInterval;
use DateTime;
use DateTimeImmutable;

class DateTimeUtilsDataProvider extends BaseDataProvider
{
    public static function compareDateIntervalsDataCases()
    {
        return [
            [
                new DateInterval('PT1H'),
                new DateInterval('PT2H'),
                -1
            ],
            [
                new DateInterval('PT1H'),
                new DateInterval('PT1H'),
                0
            ],
            [
                new DateInterval('PT2H'),
                new DateInterval('PT1H'),
                1
            ],
        ];
    }

    public static function resolveDateTimeImmutableWithDefaultDataCases()
    {
        return [
            [null, new DateTimeImmutable('2025-10-10'), '2025-10-10'],
            ['2025-11-01', new DateTimeImmutable('2025-10-10'), '2025-11-01'],
            [new DateTime('2025-12-01'), new DateTimeImmutable('2025-10-10'), '2025-12-01'],
            [new DateTimeImmutable('2025-12-01'), new DateTimeImmutable('2025-10-10'), '2025-12-01'],
        ];
    }

    public static function subtractTimeWindowDataCases()
    {
        return [
            [
                new TimeWindow(new DateTimeImmutable('2025-10-01 08:00'), new DateTimeImmutable('2025-10-01 16:00')),
                new TimeWindow(new DateTimeImmutable('2025-10-01 06:00'), new DateTimeImmutable('2025-10-01 07:00')),
                [
                    new TimeWindow(new DateTimeImmutable('2025-10-01 08:00'), new DateTimeImmutable('2025-10-01 16:00'))
                ],
            ],
            [
                new TimeWindow(new DateTimeImmutable('2025-10-01 08:00'), new DateTimeImmutable('2025-10-01 16:00')),
                new TimeWindow(new DateTimeImmutable('2025-10-01 06:00'), new DateTimeImmutable('2025-10-01 09:00')),
                [
                    new TimeWindow(new DateTimeImmutable('2025-10-01 09:00'), new DateTimeImmutable('2025-10-01 16:00'))
                ],
            ],
            [
                new TimeWindow(new DateTimeImmutable('2025-10-01 08:00'), new DateTimeImmutable('2025-10-01 16:00')),
                new TimeWindow(new DateTimeImmutable('2025-10-01 06:00'), new DateTimeImmutable('2025-10-01 16:00')),
                [],
            ],
            [
                new TimeWindow(new DateTimeImmutable('2025-10-01 08:00'), new DateTimeImmutable('2025-10-01 16:00')),
                new TimeWindow(new DateTimeImmutable('2025-10-01 09:00'), new DateTimeImmutable('2025-10-01 11:00')),
                [
                    new TimeWindow(new DateTimeImmutable('2025-10-01 08:00'), new DateTimeImmutable('2025-10-01 09:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 11:00'), new DateTimeImmutable('2025-10-01 16:00'))
                ],
            ],
            [
                new TimeWindow(new DateTimeImmutable('2025-10-01 08:00'), new DateTimeImmutable('2025-10-01 16:00')),
                new TimeWindow(new DateTimeImmutable('2025-10-01 12:00'), new DateTimeImmutable('2025-10-01 17:00')),
                [
                    new TimeWindow(new DateTimeImmutable('2025-10-01 08:00'), new DateTimeImmutable('2025-10-01 12:00')),
                ],
            ],
        ];
    }

    public static function timeWindowCollectionDiffDataCases()
    {
        return [
            [
                [
                    new TimeWindow(new DateTimeImmutable('2025-10-01 08:00'), new DateTimeImmutable('2025-10-01 12:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 13:00'), new DateTimeImmutable('2025-10-01 15:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 18:00'), new DateTimeImmutable('2025-10-01 20:00')),
                ],
                [
                    new TimeWindow(new DateTimeImmutable('2025-10-01 11:00'), new DateTimeImmutable('2025-10-01 14:30')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 18:00'), new DateTimeImmutable('2025-10-01 19:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 09:00'), new DateTimeImmutable('2025-10-01 10:00')),
                ],
                [
                    new TimeWindow(new DateTimeImmutable('2025-10-01 08:00'), new DateTimeImmutable('2025-10-01 09:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 10:00'), new DateTimeImmutable('2025-10-01 11:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 14:30'), new DateTimeImmutable('2025-10-01 15:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 19:00'), new DateTimeImmutable('2025-10-01 20:00')),
                ]
            ],
        ];
    }

    public static function sortTimeWindowCollectionDataCases()
    {
        return [
            [
                [
                    new TimeWindow(new DateTimeImmutable('2025-10-01 14:00'), new DateTimeImmutable('2025-10-01 15:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 08:00'), new DateTimeImmutable('2025-10-01 10:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 15:00'), new DateTimeImmutable('2025-10-01 18:00')),
                ],
                [
                    new TimeWindow(new DateTimeImmutable('2025-10-01 08:00'), new DateTimeImmutable('2025-10-01 10:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 14:00'), new DateTimeImmutable('2025-10-01 15:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 15:00'), new DateTimeImmutable('2025-10-01 18:00')),
                ]
            ]
        ];
    }

    public static function mergeAdjacentTimeWindowsDataCases()
    {
        return [
            [
                [
                    new TimeWindow(new DateTimeImmutable('2025-10-01 19:00'), new DateTimeImmutable('2025-10-01 20:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 14:00'), new DateTimeImmutable('2025-10-01 15:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-02 00:00'), new DateTimeImmutable('2025-10-02 05:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 08:00'), new DateTimeImmutable('2025-10-01 10:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 15:00'), new DateTimeImmutable('2025-10-01 18:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 21:00'), new DateTimeImmutable('2025-10-02 00:00')),
                ],
                [
                    new TimeWindow(new DateTimeImmutable('2025-10-01 08:00'), new DateTimeImmutable('2025-10-01 10:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 14:00'), new DateTimeImmutable('2025-10-01 18:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 19:00'), new DateTimeImmutable('2025-10-01 20:00')),
                    new TimeWindow(new DateTimeImmutable('2025-10-01 21:00'), new DateTimeImmutable('2025-10-02 05:00')),
                ]
            ]
        ];
    }
}