<?php

namespace App\Service\EntitySerializer;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EntitySerializer implements EntitySerializerInterface
{
    public function __construct(private NormalizerInterface $normalizer, private DenormalizerInterface $denormalizer)
    {
        
    }

    /**
     * @template T of object
     * @param array $params
     * @param class-string<T>|T $targetEntity
     * @return T
    */
    public function parseToEntity(array $params, string|object $targetEntity): object
    {
        $context = is_object($targetEntity) ? [
            AbstractNormalizer::OBJECT_TO_POPULATE => $targetEntity
        ] : [];
        $class = is_object($targetEntity) ? get_class($targetEntity) : $targetEntity;

        return $this->denormalizer->denormalize($params, $class, context: $context);
    }

    public function normalize(mixed $value, array $groups): array
    {
        return $this->normalizer->normalize($value, context: ['groups' => $groups]);
    }
}