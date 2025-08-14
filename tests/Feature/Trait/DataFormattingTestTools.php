<?php

namespace App\Tests\Feature\Trait;

trait DataFormattingTestTools
{
    protected function normalize(mixed $value, array $groups): array
    {
        return $this->normalizer->normalize($value, context: ['groups' => $groups]);
    }
}