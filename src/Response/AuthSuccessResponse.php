<?php

declare(strict_types=1);

namespace App\Response;

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