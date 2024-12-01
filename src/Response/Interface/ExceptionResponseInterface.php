<?php

namespace App\Response\Interface;

use Throwable;

interface ExceptionResponseInterface
{
    public static function createFromException(Throwable $throwable): self;
} 