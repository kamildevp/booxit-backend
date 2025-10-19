<?php

declare(strict_types=1);

namespace App\Enum\Reservation;

use App\Entity\Reservation;
use App\Enum\Interface\NormalizerGroupInterface;
use App\Enum\NormalizerGroup;
use App\Enum\Organization\OrganizationNormalizerGroup;
use App\Enum\Schedule\ScheduleNormalizerGroup;
use App\Enum\Service\ServiceNormalizerGroup;
use App\Enum\Trait\NormalizerGroupTrait;
use App\Enum\User\UserNormalizerGroup;

enum ReservationNormalizerGroup: string implements NormalizerGroupInterface
{
    use NormalizerGroupTrait;

    case DEFAULT = 'reservation-default';

    case BASE_INFO = 'reservation-base_info';
    case DETAILS = 'reservation-details';
    case SENSITIVE = 'reservation-sensitive';
    case ORGANIZATION = 'reservation-organization';
    case SCHEDULE = 'reservation-schedule';
    case SERVICE = 'reservation-service';
    case USER = 'reservation-user';
    case ORGANIZATION_ONLY = 'reservation-organization_only';
    case SCHEDULE_RESERVATIONS = 'reservation-schedule_reservations';
    case USER_RESERVATIONS = 'reservation-user_reservations';
    case TIMESTAMP = Reservation::class.NormalizerGroup::TIMESTAMP->value;
    case AUTHOR_INFO = Reservation::class.NormalizerGroup::AUTHOR_INFO->value;

    protected function appendGroups(): array
    {
        return match($this){
            self::DEFAULT => [
                self::BASE_INFO->value, 
                self::DETAILS->value,
                self::TIMESTAMP->value,
                self::SENSITIVE->value, 
                ...self::SERVICE->normalizationGroups(),
            ],
            self::SCHEDULE_RESERVATIONS => [
                ...self::DEFAULT->normalizationGroups(),
                ...self::ORGANIZATION_ONLY->normalizationGroups()
            ],
            self::USER_RESERVATIONS => [
                ...self::DEFAULT->normalizationGroups(),
                ...self::SCHEDULE->normalizationGroups(),
                ...self::ORGANIZATION->normalizationGroups(),
            ],
            self::ORGANIZATION_ONLY => [
                ...self::AUTHOR_INFO->normalizationGroups(),
                ...self::USER->normalizationGroups(),
            ],
            self::ORGANIZATION => OrganizationNormalizerGroup::BASE_INFO->normalizationGroups(),
            self::SCHEDULE => ScheduleNormalizerGroup::BASE_INFO->normalizationGroups(),
            self::SERVICE => ServiceNormalizerGroup::BASE_INFO->normalizationGroups(),
            self::USER => UserNormalizerGroup::BASE_INFO->normalizationGroups(),
            self::AUTHOR_INFO => NormalizerGroup::AUTHOR_INFO->normalizationGroups(),
            default => []
        };
    }
}