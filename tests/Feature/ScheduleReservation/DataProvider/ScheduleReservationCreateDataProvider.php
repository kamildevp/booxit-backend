<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleReservation\DataProvider;

use App\Enum\Reservation\ReservationStatus;
use App\Enum\TranslationsLocale;
use App\Tests\Utils\DataProvider\BaseDataProvider;
use App\Validator\Constraints\Compound\DateTimeStringRequirements;
use DateTimeImmutable;

class ScheduleReservationCreateDataProvider extends BaseDataProvider 
{
    
    public static function validDataCases()
    {
        $startDateTime = (new DateTimeImmutable('wednesday next week'))->setTime(2,0);
        $endDateTime = (new DateTimeImmutable('wednesday next week'))->setTime(2,30);

        return [
            [
                [
                    'email' => 'user@example.com',
                    'phone_number' => '+48213721372',
                    'start_date_time' => $startDateTime->format(DateTimeStringRequirements::FORMAT),
                    'verification_handler' => self::VERIFICATION_HANDLER,
                    'language_preference' => TranslationsLocale::EN->value,
                ],
                [
                    'email' => 'user@example.com',
                    'phone_number' => '+48213721372',
                    'start_date_time' => $startDateTime->format(DateTimeStringRequirements::FORMAT),
                    'end_date_time' => $endDateTime->format(DateTimeStringRequirements::FORMAT),
                    'estimated_price' => '20.50',
                    'status' => ReservationStatus::PENDING->value,
                    'language_preference' => TranslationsLocale::EN->value,
                ]
            ]
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
                    'start_date_time' => '',
                    'verification_handler' => '',
                    'language_preference' => '',
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
                    'start_date_time' => [
                        'This value should not be blank.'
                    ],
                    'verification_handler' => [
                        'This value should not be blank.'
                    ],
                    'language_preference' => [
                        'Parameter must be one of valid locales: '.implode(', ', array_map(fn($val) => '"'.$val.'"', TranslationsLocale::values())),
                    ],
                ]
            ],
            [
                [
                    'service_id' => 0,
                    'email' => 'a',
                    'phone_number' => 'a',
                    'start_date_time' => 'a',
                    'verification_handler' => 'a',
                    'language_preference' => 'a',
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
                    'start_date_time' => [
                        'Parameter must be datetime string in format '. DateTimeStringRequirements::FORMAT
                    ],
                    'verification_handler' => [
                        'Invalid verification handler'
                    ],
                    'language_preference' => [
                        'Parameter must be one of valid locales: '.implode(', ', array_map(fn($val) => '"'.$val.'"', TranslationsLocale::values())),
                    ],
                ]
            ],
        ];
    }
}