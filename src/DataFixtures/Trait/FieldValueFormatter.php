<?php

namespace App\DataFixtures\Trait;

trait FieldValueFormatter
{
    protected function getFieldValue(int $index, string $field, mixed $default){
        return $this->fieldValues[$index][$field] ?? $default;
    }
}
