<?php

declare(strict_types=1);

namespace App\DTO\Reservation;

use App\DTO\AbstractDTO;

class ReservationOrganizationCancelDTO extends AbstractDTO 
{
    public function __construct(
        public readonly bool $notifyCustomer,
    )
    {

    }
}