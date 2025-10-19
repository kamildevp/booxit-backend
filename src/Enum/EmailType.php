<?php

declare(strict_types=1);

namespace App\Enum;

use App\Enum\Trait\ValuesTrait;

enum EmailType: string
{
    use ValuesTrait;

    case ACCOUNT_ACTIVATION = 'account_activation-email';
    case EMAIL_VERIFICATION = 'email_verification-email';
    case PASSWORD_RESET = 'password_reset-email';

    public function getSubject(): string
    {
        return match($this){
            self::ACCOUNT_ACTIVATION => 'Account activation',
            self::EMAIL_VERIFICATION => 'Email Verification',
            self::PASSWORD_RESET => 'Password Reset'
        };
    }

    public function getTemplatePath(): string
    {
        return match($this){
            self::ACCOUNT_ACTIVATION => 'emails/account_activation.html.twig',
            self::EMAIL_VERIFICATION => 'emails/email_verification.html.twig',
            self::PASSWORD_RESET => 'emails/password_reset.html.twig'
        };
    }
}