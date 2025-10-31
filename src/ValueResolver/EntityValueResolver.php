<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Exceptions\EntityNotFoundException;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityValueResolver implements ValueResolverInterface
{
    public function __construct(private ValueResolverInterface $inner) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $entityClass = $argument->getType();

        try{
            return $this->inner->resolve($request, $argument);
        }
        catch(NotFoundHttpException){
            throw new EntityNotFoundException($entityClass);
        }
    }
}

