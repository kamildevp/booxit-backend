<?php

declare(strict_types=1);

namespace App\Tests\Feature\UserReservation\DataProvider;

use App\Enum\Reservation\ReservationStatus;
use App\Tests\Utils\DataProvider\BaseDataProvider;
use App\Validator\Constraints\Compound\DateTimeStringRequirements;
use DateTimeImmutable;
use DateTimeZone;

class UserReservationCreateDataProvider extends BaseDataProvider 
{
    
    public static function validDataCases()
    {
        $timezone = new DateTimeZone('Europe/Warsaw');
        $timezoneUTC = new DateTimeZone('UTC');
        $startDateTime = (new DateTimeImmutable('wednesday next week', $timezone))->setTime(3,0);
        $endDateTime = (new DateTimeImmutable('wednesday next week', $timezone))->setTime(3,30);

        return [
            [
                [
                    'phone_number' => '+48213721372',
                    'start_date_time' => $startDateTime->format(DateTimeStringRequirements::FORMAT),
                    'verification_handler' => self::VERIFICATION_HANDLER,
                ],
                [
                    'phone_number' => '+48213721372',
                    'start_date_time' => $startDateTime->setTimezone($timezoneUTC)->format(DateTimeStringRequirements::FORMAT),
                    'end_date_time' => $endDateTime->setTimezone($timezoneUTC)->format(DateTimeStringRequirements::FORMAT),
                    'estimated_price' => '20.50',
                    'status' => ReservationStatus::PENDING->value,
                ]
            ]
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'schedule_id' => 0,
                    'service_id' => 0,
                    'phone_number' => '',
                    'start_date_time' => '',
                    'verification_handler' => '',
                ],
                [
                    'schedule_id' => [
                        'Schedule does not exist',
                    ],
                    'service_id' => [
                        'Service does not exist',
                    ],
                    'phone_number' => [
                        'This value should not be blank.',
                    ],
                    'start_date_time' => [
                        'This value should not be blank.'
                    ],
                    'verification_handler' => [
                        'This value should not be blank.'
                    ]
                ]
            ],
            [
                [
                    'schedule_id' => 0,
                    'service_id' => 0,
                    'phone_number' => 'a',
                    'start_date_time' => 'a',
                    'verification_handler' => 'a',
                ],
                [
                    'schedule_id' => [
                        'Schedule does not exist',
                    ],
                    'service_id' => [
                        'Service does not exist',
                    ],
                    'phone_number' => [
                        'This value is not a valid phone number.',
                    ],
                    'start_date_time' => [
                        'Parameter must be datetime string in format '. DateTimeStringRequirements::FORMAT
                    ],
                    'verification_handler' => [
                        'Invalid verification handler'
                    ]
                ]
            ],
        ];
    }
}