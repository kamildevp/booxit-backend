<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Repository\RepositoryUtilsInterface;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;

class EntityValueResolver implements ValueResolverInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $entityClass = $argument->getType();
        $id = $request->attributes->get($argument->getName());

        if (!$id || !$entityClass || !class_exists($entityClass)) {
            return [];
        }

        try {
            $metadata = $this->entityManager->getClassMetadata($entityClass);
        } catch (MappingException) {
            return [];
        }

        
        $repository = $this->entityManager->getRepository($entityClass);
        if(!$repository instanceof RepositoryUtilsInterface){
            return [];
        }

        $entity = $repository->findOrFail($id);

        return [$entity];
    }
}

