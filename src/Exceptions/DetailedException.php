<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class DetailedException extends RuntimeException
{
    protected mixed $data;   

    public function __construct(string $message = '', mixed $data = null)
    {
        $this->data = $data;
        parent::__construct($message);
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}