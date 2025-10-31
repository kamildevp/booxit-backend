<?php

declare(strict_types=1);

namespace App\Tests\Utils\Trait;

trait DataFormattingTestTools
{
    protected function normalize(mixed $value, array $groups): array
    {
        return $this->normalizer->normalize($value, context: ['groups' => $groups]);
    }
}