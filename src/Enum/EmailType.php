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
    case RESERVATION_VERIFICATION = 'reservation_verification-email';
    case RESERVATION_SUMMARY = 'reservation_summary-email';
    case RESERVATION_CONFIRMATION = 'reservation_confirmation-email';
    case RESERVATION_UPDATED_NOTIFICATION = 'reservation_updated_notification-email';
    case RESERVATION_CANCELLED_NOTIFICATION = 'reservation_cancelled_notification-email';

    public function getSubject(): string
    {
        return match($this){
            self::ACCOUNT_ACTIVATION => 'account_activation.subject',
            self::EMAIL_VERIFICATION => 'email_verification.subject',
            self::PASSWORD_RESET => 'password_reset.subject',
            self::RESERVATION_VERIFICATION => 'reservation_verification.subject',
            self::RESERVATION_SUMMARY => 'reservation_summary.subject',
            self::RESERVATION_CONFIRMATION => 'reservation_confirmed.subject',
            self::RESERVATION_UPDATED_NOTIFICATION => 'reservation_updated.subject',
            self::RESERVATION_CANCELLED_NOTIFICATION => 'reservation_cancelled.subject',
        };
    }

    public function getTemplatePath(): string
    {
        return match($this){
            self::ACCOUNT_ACTIVATION => 'emails/account_activation.html.twig',
            self::EMAIL_VERIFICATION => 'emails/email_verification.html.twig',
            self::PASSWORD_RESET => 'emails/password_reset.html.twig',
            self::RESERVATION_VERIFICATION => 'emails/reservation_verification.html.twig',
            self::RESERVATION_SUMMARY => 'emails/reservation_summary.html.twig',
            self::RESERVATION_CONFIRMATION => 'emails/reservation_confirmation.html.twig',
            self::RESERVATION_UPDATED_NOTIFICATION => 'emails/reservation_updated_notification.html.twig',
            self::RESERVATION_CANCELLED_NOTIFICATION => 'emails/reservation_cancelled_notification.html.twig',
        };
    }
}