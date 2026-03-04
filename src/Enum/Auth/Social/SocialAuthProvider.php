<?php

declare(strict_types=1);

namespace App\Enum\Auth\Social;

enum SocialAuthProvider: string
{
    case GOOGLE = 'GOOGLE';
}