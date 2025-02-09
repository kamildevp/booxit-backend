<?php

namespace App\Response;

use App\Enum\ResponseStatus;

class AuthSuccessResponse extends SuccessResponse
{

    public function __construct(string $accessToken, string $refreshToken, array $headers = [])
    {
        parent::__construct([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken
            ]);
    }
} 