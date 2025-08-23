<?php

declare(strict_types=1);

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse extends JsonResponse
{
    private mixed $rawData;

    public function __construct(mixed $data = null, int $statusCode = 200, array $headers = [])
    {
        $this->rawData = $data;
        parent::__construct($data, $statusCode, $headers);
    }

    public function getRawData(): mixed
    {
        return $this->rawData;
    }
} 