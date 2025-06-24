<?php

namespace App\DTO\EmailConfirmation;

use App\DTO\AbstractDTO;
use App\DTO\EmailConfirmation\Trait\EmailConfirmationBaseDTOFields;

class VerifyEmailConfirmationDTO extends AbstractDTO
{
    use EmailConfirmationBaseDTOFields;

    public function __construct(
        int $id,
        int $expires,
        string $type,
        string $token,
        string $signature,
    ) {
        $this->id = $id;
        $this->expires = $expires;
        $this->type = $type;
        $this->token = $token;
        $this->signature = $signature;
    }
}