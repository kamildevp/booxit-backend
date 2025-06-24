<?php 

namespace App\Service\EntitySerializer;

interface EntitySerializerInterface 
{
    /**
     * @template T of object
     * @param array $params
     * @param class-string<T>|T $targetEntity
     * @return T
    */
    public function parseToEntity(array $params, string | object $targetEntity): object;

    public function normalize(mixed $value, array $groups): array;
}