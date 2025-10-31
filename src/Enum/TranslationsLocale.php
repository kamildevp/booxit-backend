<?php

declare(strict_types=1);

namespace App\Enum;

use App\Enum\Trait\ValuesTrait;

enum TranslationsLocale: string
{
    use ValuesTrait;

    case EN = 'en';
    case PL = 'pl';
}