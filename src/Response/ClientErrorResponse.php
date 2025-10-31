<?php

declare(strict_types=1);

namespace App\Response;

use App\Enum\ResponseStatus;
use App\Exceptions\DetailedException;
use App\Response\Interface\ExceptionResponseInterface;
use Throwable;

class ClientErrorResponse extends ApiResponse implements ExceptionResponseInterface
{
    public const RESPONSE_STATUS = 400;

    public function __construct(int $statusCode = self::RESPONSE_STATUS, string $message = '', mixed $errors = null, array $headers = [])
    {
        parent::__construct([
            'status' => ResponseStatus::FAIL, 
            'data' => [
                'message' => $message,
                'errors' => $errors
            ]
        ], $statusCode, $headers);
    }

    public static function createFromException(Throwable $exception): self
    {
        $errors = $exception instanceof DetailedException ? $exception->getData() : null;

        return new self(static::RESPONSE_STATUS, $exception->getMessage(), $errors);
    }


} 