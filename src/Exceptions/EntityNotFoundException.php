<?php

declare(strict_types=1);

namespace App\Exceptions;

use ReflectionClass;
use RuntimeException;
use Throwable;

class EntityNotFoundException extends RuntimeException
{
    protected string $entityClass; 

    public function __construct(string $entityClass = "", int $code = 0, Throwable|null $previous = null)
    {
        $entityName = !empty($entityClass) ? (new ReflectionClass($entityClass))->getShortName() : '';
        $message = !empty($entityName) ? $entityName . ' not found' : 'Not found';
        
        parent::__construct($message, $code, $previous);
    }

}