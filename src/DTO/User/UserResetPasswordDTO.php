<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\DTO\AbstractDTO;
use App\DTO\EmailConfirmation\Trait\EmailConfirmationBaseDTOFields;
use App\DTO\User\Trait\UserPasswordFieldDTO;
use App\Enum\EmailConfirmation\EmailConfirmationType;

class UserResetPasswordDTO extends AbstractDTO
{
    use EmailConfirmationBaseDTOFields, UserPasswordFieldDTO;

    public function __construct(
        int $id,
        int $expires,
        string $type,
        string $token,
        string $_hash,
        string $password
    ) {
        $this->id = $id;
        $this->expires = $expires;
        $this->type = $type;
        $this->token = $token;
        $this->_hash = $_hash;
        $this->password = $password;
    }

    public static function getAllowedTypes(): array
    {
        return [EmailConfirmationType::PASSWORD_RESET->value];
    }
}