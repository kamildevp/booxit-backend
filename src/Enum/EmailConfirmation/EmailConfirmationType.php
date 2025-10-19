<?php

declare(strict_types=1);

namespace App\Enum\EmailConfirmation;

use App\Enum\Trait\ValuesTrait;

enum EmailConfirmationType: string
{
    use ValuesTrait;

    case ACCOUNT_ACTIVATION = 'account_activation';
    case EMAIL_VERIFICATION = 'email_verification';
    case PASSWORD_RESET = 'password_reset';
    case RESERVATION_VERIFICATION = 'reservation_verification';
}