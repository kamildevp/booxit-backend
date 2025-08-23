<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\DTO\AbstractDTO;
use App\DTO\User\Trait\UserPasswordFieldDTO;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class UserChangePasswordDTO extends AbstractDTO 
{
    use UserPasswordFieldDTO;

    #[SecurityAssert\UserPassword(
        message: 'Invalid current password',
    )]
    public readonly string $oldPassword;

    public bool $logoutOtherSessions;

    public function __construct(string $password, string $oldPassword, bool $logoutOtherSessions)
    {
        $this->password = $password;
        $this->oldPassword = $oldPassword;
        $this->logoutOtherSessions = $logoutOtherSessions;
    }
}