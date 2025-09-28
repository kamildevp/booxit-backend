<?php

declare(strict_types=1);

namespace App\Enum;

use App\Enum\Trait\ValuesTrait;

enum EmailConfirmationType: string
{
    use ValuesTrait;

    case USER_VERIFICATION = 'user_verification';
    case EMAIL_VERIFICATION = 'email_verification';
    case PASSWORD_RESET = 'password_reset';
}