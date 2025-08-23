<?php

declare(strict_types=1);

namespace App\Enum;

enum EmailConfirmationType: string
{
    case USER_VERIFICATION = 'user_verification';
    case EMAIL_VERIFICATION = 'email_verification';
    case PASSWORD_RESET = 'password_reset';
}