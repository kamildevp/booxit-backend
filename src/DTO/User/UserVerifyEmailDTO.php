<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\DTO\AbstractDTO;
use App\DTO\EmailConfirmation\Trait\EmailConfirmationBaseDTOFields;
use App\Enum\EmailConfirmationType;

class UserVerifyEmailDTO extends AbstractDTO
{
    use EmailConfirmationBaseDTOFields;

    public function __construct(
        int $id,
        int $expires,
        string $type,
        string $token,
        string $_hash,
    ) {
        $this->id = $id;
        $this->expires = $expires;
        $this->type = $type;
        $this->token = $token;
        $this->_hash = $_hash;
    }
    
    public static function getAllowedTypes(): array
    {
        return [EmailConfirmationType::USER_VERIFICATION->value, EmailConfirmationType::EMAIL_VERIFICATION->value];
    }
}