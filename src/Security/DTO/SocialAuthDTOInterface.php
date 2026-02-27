<?php

declare(strict_types=1);

namespace App\Security\DTO;

interface SocialAuthDTOInterface
{
    public function getAuthHandler(): string;

    public function getCode(): string;

    public function getCodeVerifier(): string;
}