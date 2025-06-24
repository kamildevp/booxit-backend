<?php

namespace App\DTO\EmailConfirmation\Trait;

use Symfony\Component\Validator\Constraints as Assert;

trait EmailConfirmationBaseDTOFields {
    #[Assert\NotBlank]
    #[Assert\Type('int')]
    public readonly int $id;

    #[Assert\NotBlank]
    #[Assert\Type('int')]
    public readonly int $expires;

    #[Assert\NotBlank]
    public readonly string $type;

    #[Assert\NotBlank]
    public readonly string $token;

    #[Assert\NotBlank]
    public readonly string $signature;
}