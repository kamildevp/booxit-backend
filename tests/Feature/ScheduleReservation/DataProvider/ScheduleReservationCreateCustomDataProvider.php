<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleReservation\DataProvider;

use App\Enum\Reservation\ReservationStatus;
use App\Tests\Utils\DataProvider\BaseDataProvider;
use App\Validator\Constraints\Compound\DateTimeStringRequirements;
use DateTimeImmutable;

class ScheduleReservationCreateCustomDataProvider extends BaseDataProvider 
{
    
    public static function validDataCases()
    {
        $startDateTime = (new DateTimeImmutable('wednesday next week'))->setTime(15,0);
        $endDateTime = (new DateTimeImmutable('wednesday next week'))->setTime(15,30);

        return [
            [
                [
                    'email' => 'user@example.com',
                    'phone_number' => '+48213735572',
                    'estimated_price' => '31.50',
                    'start_date_time' => $startDateTime->format(DateTimeStringRequirements::FORMAT),
                    'end_date_time' => $endDateTime->format(DateTimeStringRequirements::FORMAT),
                    'status' => ReservationStatus::CONFIRMED->value,
                ],
                [
                    'email' => 'user@example.com',
                    'phone_number' => '+48213735572',
                    'estimated_price' => '31.50',
                    'start_date_time' => $startDateTime->format(DateTimeStringRequirements::FORMAT),
                    'end_date_time' => $endDateTime->format(DateTimeStringRequirements::FORMAT),
                    'status' => ReservationStatus::CONFIRMED->value,
                ],
            ],
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'service_id' => 0,
                    'email' => '',
                    'phone_number' => '',
                    'estimated_price' => '',
                    'start_date_time' => '',
                    'end_date_time' => '',
                    'status' => '',
                ],
                [
                    'service_id' => [
                        'Service does not exist',
                    ],
                    'email' => [
                        'This value should not be blank.',
                    ],
                    'phone_number' => [
                        'This value should not be blank.',
                    ],
                    'estimated_price' => [
                        'This value should not be blank.',
                    ],
                    'start_date_time' => [
                        'This value should not be blank.'
                    ],
                    'end_date_time' => [
                        'This value should not be blank.'
                    ],
                    'status' => [
                        'Parameter must be one of valid statuses: '.implode(', ', array_map(fn($val) => '"'.$val.'"', ReservationStatus::values())),
                    ],
                ]
            ],
            [
                [
                    'service_id' => 0,
                    'email' => 'a',
                    'phone_number' => 'a',
                    'estimated_price' => 'a',
                    'start_date_time' => 'a',
                    'end_date_time' => 'a',
                    'status' => 'a',
                    'notify_customer' => true,
                    'verification_handler' => 'a',
                ],
                [
                    'service_id' => [
                        'Service does not exist',
                    ],
                    'email' => [
                        'Parameter is not a valid email',
                    ],
                    'phone_number' => [
                        'This value is not a valid phone number.',
                    ],
                    'estimated_price' => [
                        'Parameter must be a valid number with up to 2 decimals.',
                    ],
                    'start_date_time' => [
                        'Parameter must be datetime string in format '. DateTimeStringRequirements::FORMAT
                    ],
                    'end_date_time' => [
                        'Parameter must be datetime string in format '. DateTimeStringRequirements::FORMAT
                    ],
                    'status' => [
                        'Parameter must be one of valid statuses: '.implode(', ', array_map(fn($val) => '"'.$val.'"', ReservationStatus::values())),
                    ],
                ]
            ],
        ];
    }
}