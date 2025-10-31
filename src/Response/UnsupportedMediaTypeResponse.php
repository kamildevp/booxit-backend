<?php

declare(strict_types=1);

namespace App\Response;

use App\Enum\ResponseStatus;
use App\Response\Interface\ExceptionResponseInterface;
use Throwable;

class UnsupportedMediaTypeResponse extends ApiResponse implements ExceptionResponseInterface
{
    public const RESPONSE_STATUS = 415;
    public const RESPONSE_MESSAGE = 'Unsupported media type';

    public function __construct(string $message = self::RESPONSE_MESSAGE, array $headers = [])
    {
        parent::__construct([
            'status' => ResponseStatus::FAIL, 
            'data' => [
                'message' => $message,
            ]
        ], self:: RESPONSE_STATUS, $headers);
    }

    public static function createFromException(Throwable $exception): self
    {
        return new self($exception->getMessage());
    }
} 