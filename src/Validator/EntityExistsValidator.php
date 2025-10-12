<?php

declare(strict_types=1);

namespace App\Validator;

use App\Validator\Constraints\EntityExists;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class EntityExistsValidator extends ConstraintValidator
{
    private Request $request;
    private array $requestContent;

    public function __construct(
        private EntityManagerInterface $entityManager, 
        private RequestStack $requestStack,
    ) {
        $this->request = $this->requestStack->getCurrentRequest();
        $this->requestContent = json_decode($this->request->getContent(), true) ?? [];
    }

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

        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($constraint->entityClass, 'e')
            ->where('e.'.$constraint->fieldName.' = :fieldValue')
            ->setParameter('fieldValue', $value);

        $this->applyRelatedTo($qb, $constraint->entityClass, $constraint->relatedTo);
        $this->applyCommonRelations($qb, $constraint->commonRelations);

        $matchingEntity = $qb->getQuery()->getOneOrNullResult();

        if (empty($matchingEntity)) {
            $shortName = (new \ReflectionClass($constraint->entityClass))->getShortName();
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ entityClass }}', $shortName)
                ->addViolation();
        }
    }

    private function applyRelatedTo(QueryBuilder $qb, string $entityClass, array $relatedTo): void
    {
        $relations = array_map(fn($param) => $this->getRequestParam($param), $relatedTo);
        $relationIndx = 0;
        foreach($relations as $relation => $relatedId){
            if(is_null($relatedId)){
                continue;
            }

            $isCollection = $this->entityManager
                ->getClassMetadata($entityClass)
                ->isCollectionValuedAssociation($relation);
            if($isCollection){
                $qbIdentifier = "cr$relationIndx";
                $qbParameter = "crp$relationIndx";
                $qb->innerJoin("e.$relation", $qbIdentifier)
                    ->andWhere("$qbIdentifier = :$qbParameter")
                    ->setParameter($qbParameter, $relatedId);
                $relationIndx++;
            }
            else{
                $qb->andWhere("e.$relation = :relatedTo")->setParameter('relatedTo', $relatedId);
            }
        }
    }

    private function applyCommonRelations(QueryBuilder $qb, array $commonRelations): void
    {
        $commonRelations = array_map(fn($mapping) => [
                $mapping[0], 
                $this->getRequestParam($mapping[1]), 
                $mapping[2] ?? 'id'
            ], 
            $commonRelations
        );

        $relationIndx = 0;
        foreach($commonRelations as $relatedThrough => [$commonRelationProperty, $relatedId, $relatedIdentifierField]){
            if(is_null($relatedId)){
                continue;
            }

            $qbRelatedThroughIdentifier = "crt$relationIndx";
            $qbRelatedIdentifier = "cr$relationIndx";
            $qbRelatedIdentifierParameter = "crp$relationIndx";
            $qb->innerJoin("e.$relatedThrough", $qbRelatedThroughIdentifier)
                ->innerJoin("$qbRelatedThroughIdentifier.$commonRelationProperty", $qbRelatedIdentifier)
                ->andWhere("$qbRelatedIdentifier.$relatedIdentifierField = :$qbRelatedIdentifierParameter")
                ->setParameter($qbRelatedIdentifierParameter, $relatedId);
            $relationIndx++;
        }
    }

    private function getRequestParam(string $value): mixed
    {
        $matches = [];
        if(preg_match('/^\{route:([A-Za-z_][A-Za-z0-9_]+)\}$/', $value, $matches)){
            $value = $this->request->attributes->get($matches[1]);
        }
        elseif(preg_match('/^\{body:([A-Za-z_][A-Za-z0-9_]+)\}$/', $value, $matches)){
            $value = $requestContent[$matches[1]] ?? null;
        }

        return is_string($value) || is_int($value) ? $value : null;
    }
}