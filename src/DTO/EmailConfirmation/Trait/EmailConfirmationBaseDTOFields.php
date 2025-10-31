<?php

declare(strict_types=1);

namespace App\DTO\EmailConfirmation\Trait;

use Symfony\Component\Validator\Constraints as Assert;

trait EmailConfirmationBaseDTOFields 
{
    public readonly int $id;

    public readonly int $expires;

    #[Assert\Choice(callback: 'getAllowedTypes', message: 'Parameter must be one of valid types: {{ choices }}')]
    public readonly string $type;

    #[Assert\NotBlank]
    public readonly string $token;

    #[Assert\NotBlank]
    public readonly string $_hash;

    abstract static function getAllowedTypes(): array;
}