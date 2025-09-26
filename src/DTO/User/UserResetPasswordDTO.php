<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\DTO\EmailConfirmation\VerifyEmailConfirmationDTO;
use App\DTO\User\Trait\UserPasswordFieldDTO;

class UserResetPasswordDTO extends VerifyEmailConfirmationDTO
{
    use UserPasswordFieldDTO;

    public function __construct(
        int $id,
        int $expires,
        string $type,
        string $token,
        string $_hash,
        string $password
    ) {
        parent::__construct($id, $expires, $type, $token, $_hash);
        $this->password = $password;
    }
}