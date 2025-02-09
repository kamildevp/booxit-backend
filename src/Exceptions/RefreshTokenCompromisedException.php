<?php

namespace App\Exceptions;

use App\Entity\User;
use RuntimeException;

class RefreshTokenCompromisedException extends RuntimeException
{
    public function __construct(private User $user)
    {
        parent::__construct('Refresh token compromised');
    }

    public function getUser(): User
    {
        return $this->user;
    }
}