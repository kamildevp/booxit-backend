<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use App\DTO\AbstractDTO;

class AuthLogoutDTO extends AbstractDTO 
{
    public function __construct(public bool $logoutOtherSessions)
    {

    }
}