<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Serializer\Attribute\ResourceUrl;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResourceUrlNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
        private UrlGeneratorInterface $urlGenerator,
        private ClassMetadataFactoryInterface $classMetadataFactory,
    ) 
    {

    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        $normalized = $this->normalizer->normalize($object, $format, $context);
        $metadata = $this->classMetadataFactory->getMetadataFor($object)->getAttributesMetadata();

        $refClass = new ReflectionClass($object);
        foreach ($refClass->getProperties() as $property) {
            $attributes = $property->getAttributes(ResourceUrl::class);
            if(empty($attributes)) 
            {
                continue;
            }

            $attr = $attributes[0]->newInstance();
            if(!is_null($property->getValue($object))){
                $url = $this->resolveResourceUrl($object, $refClass, $attr->routeName, $attr->parameters);
            }
            else{
                $url = null;
            }

            $propertyName = $property->getName();
            $propertyMetadata = $metadata[$propertyName] ?? null;
            
            $serializedName = $propertyMetadata instanceof AttributeMetadata && !empty($propertyMetadata->serializedName) ? $propertyMetadata->serializedName : $propertyName;
            $normalized[$serializedName] = $url;
        }

        return $normalized;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
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
            if ($property->getAttributes(ResourceUrl::class)) {
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

    private function resolveResourceUrl(mixed $object, ReflectionClass $refClass, string $routeName, array $routeParams)
    {
        $parameters = [];
        foreach($routeParams as $name => $value){
            if(!is_string($value)){
                continue;
            }

            $resolvedValue = $this->resolveRouteParamValue($object, $refClass, $value);
            if(!empty($resolvedValue)){
                $parameters[$name] = $resolvedValue;
            }
        }

        return $this->urlGenerator->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function resolveRouteParamValue(mixed $object, ReflectionClass $refClass, string $value): mixed
    {
        $matches = [];
        if(preg_match('/^\{([A-Z_]+)\}$/i', $value, $matches)){
            $propertyName = $matches[1];
            if($refClass->hasProperty($propertyName)){
                $property = $refClass->getProperty($propertyName);
                $value = $property->getValue($object);
            }
        }

        return is_string($value) || is_int($value) ? $value : null;
    }
}
