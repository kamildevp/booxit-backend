<?php

declare(strict_types=1);

namespace App\Response;

use App\Enum\ResponseStatus;
use App\Exceptions\DetailedException;
use App\Response\Interface\ExceptionResponseInterface;
use Throwable;

class ServerErrorResponse extends ApiResponse implements ExceptionResponseInterface
{
    public const RESPONSE_STATUS = 500;
    public const RESPONSE_MESSAGE = 'Server Error';

    public function __construct(
        string $message = self::RESPONSE_MESSAGE, 
        int $statusCode = 500, 
        mixed $data = null, 
        ?int $errorCode = null, 
        array $headers = []
        )
    {
        parent::__construct([
            'status' => ResponseStatus::ERROR, 
            'message' => $message, 
            'data' => $data,
            'code' => $errorCode 
        ], $statusCode, $headers);
    }

    public static function createFromException(Throwable $exception):self
    {
        $data = $exception instanceof DetailedException ? $exception->getData() : null;

        return new self($exception->getMessage(), static::RESPONSE_STATUS, $data);
    }
} 