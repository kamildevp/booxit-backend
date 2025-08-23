<?php

declare(strict_types=1);

namespace App\DTO\Trait;

use DateTimeImmutable;

trait TimestampsFiltersFieldsDTO 
{
    public readonly ?DateTimeImmutable $createdFrom;

    public readonly ?DateTimeImmutable $createdTo;

    public readonly ?DateTimeImmutable $updatedFrom;

    public readonly ?DateTimeImmutable $updatedTo;
}