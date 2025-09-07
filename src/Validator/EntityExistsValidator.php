<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\RepositoryUtilsInterface;
use App\Validator\Constraints\EntityExists;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;
use InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class EntityExistsValidator extends ConstraintValidator
{
    public function __construct(
        private EntityManagerInterface $entityManager, 
        private RequestStack $requestStack,
        private Security $security,
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof EntityExists) {
            throw new UnexpectedTypeException($constraint, EntityExists::class);
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

        $matchingEntity = $repository->findOneByFieldValue($constraint->fieldName, $value);

        if (empty($matchingEntity)) {
            $shortName = (new \ReflectionClass($constraint->entityClass))->getShortName();
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ entityClass }}', $shortName)
                ->addViolation();
        }
    }
}