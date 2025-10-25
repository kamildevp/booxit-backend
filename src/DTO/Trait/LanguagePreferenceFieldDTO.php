<?php

declare(strict_types=1);

namespace App\DTO\Trait;

use App\Enum\TranslationsLocale;
use Symfony\Component\Validator\Constraints as Assert;

trait LanguagePreferenceFieldDTO
{
    #[Assert\Choice(callback: [TranslationsLocale::class, 'values'], message: 'Parameter must be one of valid locales: {{ choices }}')]
    public readonly string $languagePreference;
}