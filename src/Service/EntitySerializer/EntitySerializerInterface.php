<?php

declare(strict_types=1); 

namespace App\Service\EntitySerializer;

interface EntitySerializerInterface 
{
    /**
     * @template T of object
     * @param mixed $data
     * @param class-string<T>|T $targetEntity
     * @return T
    */
    public function parseToEntity(mixed $data, string | object $targetEntity): object;

    public function normalize(mixed $value, array $groups): array;
}