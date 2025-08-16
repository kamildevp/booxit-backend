<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class DateTimeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private NormalizerInterface&DenormalizerInterface $defaultNormalizer,
        private string $timezone
    ) {}

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        try{
            $datetime = $this->defaultNormalizer->denormalize($data, $type, $format, $context);
        } catch (NotNormalizableValueException $e) {
            throw NotNormalizableValueException::createForUnexpectedDataType($e->getMessage(), $data, ['datetime'], $e->getPath(), false, $e->getCode(), $e);
        }

        if ($datetime instanceof DateTimeImmutable) {
            return $datetime->setTimezone(new DateTimeZone($this->timezone));
        }

        return $datetime;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $this->defaultNormalizer->supportsDenormalization($data, $type, $format, $context);
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        return $this->defaultNormalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->defaultNormalizer->supportsNormalization($data, $format, $context);
    }
}
