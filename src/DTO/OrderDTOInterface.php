<?php

declare(strict_types=1);

namespace App\DTO;

interface OrderDTOInterface 
{
    public function getOrderMap(): array;

    public function getOrderDir(string $parameterName): ?string;

    public function hasOrder(string $parameterName): bool;
}