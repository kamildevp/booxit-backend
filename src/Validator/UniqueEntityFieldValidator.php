<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\RepositoryUtilsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use App\Validator\Constraints\UniqueEntityField;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;
use InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class UniqueEntityFieldValidator extends ConstraintValidator
{
    public function __construct(
        private EntityManagerInterface $entityManager, 
        private RequestStack $requestStack,
        private Security $security,
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEntityField) {
            throw new UnexpectedTypeException($constraint, UniqueEntityField::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value) && !is_int($value) && !is_bool($value)) {
            throw new UnexpectedValueException($value, 'string|int|bool');
        }

        try {
            $this->entityManager->getClassMetadata($constraint->entityClass);
        } catch (MappingException) {
            throw new InvalidArgumentException("Provided entity class is invalid");
        }

        
        $repository = $this->entityManager->getRepository($constraint->entityClass);
        if(!$repository instanceof RepositoryUtilsInterface){
            throw new InvalidArgumentException("Provided entity class repository must implement " . RepositoryUtilsInterface::class);
        }


        $excludeBy = $this->mapExcludeByParams($constraint->ignore);
        $matchingEntity = $repository->findOneByFieldValue($constraint->fieldName, $value, $excludeBy);

        if (!empty($matchingEntity)) {
            $shortName = (new \ReflectionClass($constraint->entityClass))->getShortName();
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ entityClass }}', $shortName)
                ->setParameter('{{ fieldName }}', $constraint->fieldName)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }

    protected function mapExcludeByParams(array $ignore): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $mappedParams = [];
        /** @var ?User $currentUser */
        $currentUser = $this->security->getUser();
        
        foreach($ignore as $column => $routeAttribute){
            if($routeAttribute == 'currentUser' && $currentUser == null){
                continue;
            }
            $column = $routeAttribute == 'currentUser' ? 'id' : $column;
            $mappedParams[$column] = $routeAttribute == 'currentUser' ? $currentUser->getId() : $request->attributes->get($routeAttribute);
        }
        return array_filter($mappedParams, fn($param) => !is_null($param));
    }
}