<?php

declare(strict_types=1);

namespace App\Response\Interface;

use App\Response\ApiResponse;
use Throwable;

interface ExceptionResponseInterface
{
    public static function createFromException(Throwable $throwable): ApiResponse;
} 