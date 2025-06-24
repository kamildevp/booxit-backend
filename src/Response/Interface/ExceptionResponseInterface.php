<?php

namespace App\Response\Interface;

use App\Response\ApiResponse;
use Throwable;

interface ExceptionResponseInterface
{
    public static function createFromException(Throwable $throwable): ApiResponse;
} 