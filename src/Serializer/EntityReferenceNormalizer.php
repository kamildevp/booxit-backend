<?php

declare(strict_types=1);

namespace App\Serializer;

use App\DTO\Attribute\EntityReference;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Throwable;

class EntityReferenceNormalizer implements DenormalizerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire(service: 'serializer.normalizer.object')]
        private DenormalizerInterface $denormalizer
    ) {}

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        try{
            $denormalized = $this->denormalizer->denormalize($data, $type, $format, $context);
            
            $refClass = new ReflectionClass($data);
            foreach ($refClass->getProperties() as $property) {
                $attributes = $property->getAttributes(EntityReference::class);
                if (empty($attributes)) {
                    continue;
                }

                $attr = $attributes[0]->newInstance();
                $value = $this->entityManager->getReference($attr->entityClass, $property->getValue($data));
                $name = $attr->alias ?? $property->getName();

                $setter = 'set' . ucfirst($name);
                if (method_exists($denormalized, $setter)) {
                    $denormalized->$setter($value);
                }
                else{
                    throw NotNormalizableValueException::createForUnexpectedDataType(
                        "Setter called $setter does not exist", 
                        $data, 
                        [$type], 
                        $context['path'] ?? null, 
                        false,
                    );
                }
            }

            return $denormalized;
        }
        catch (NotNormalizableValueException $e) {
            throw $e;
        }
        catch (Throwable $e) {
            throw NotNormalizableValueException::createForUnexpectedDataType($e->getMessage(), $data, [$type], $context['path'] ?? null, false, $e->getCode(), $e);
        }
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if(!is_object($data)){
            return false;
        }

        try {
            $refClass = new ReflectionClass($data);
        } catch (ReflectionException) {
            return false;
        }

        foreach ($refClass->getProperties() as $property) {
            if ($property->getAttributes(EntityReference::class)) {
                return true;
            }
        }

        return false;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => true,
        ];
    }
}
