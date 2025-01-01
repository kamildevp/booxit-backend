<?php

namespace App\Service\EntityHandler;

use App\Exceptions\RequestValidationException;
use App\Service\SetterHelper\SetterHelperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityHandler implements EntityHandlerInterface {

    public function __construct(
        protected ValidatorInterface $validator, 
        protected SetterHelperInterface $setterHelper, 
        protected EntityManagerInterface $entityManager 
    )
    {
        
    }

    public function parseParamsToEntity(
        object $entity, 
        array $requestParams, 
        array $requiredParameterGroups = [],
        array $allowedParameterGroups = ['Default']
    ): void
    {
        $setterHelper = $this->setterHelper->newInstance();

        $setterHelper->updateObjectSettings($entity, $requestParams, $requiredParameterGroups, $allowedParameterGroups);
        $validationErrors = $setterHelper->getValidationErrors();

        $violations = $this->validator->validate($entity, groups: $setterHelper->getValidationGroups());

        foreach ($violations as $violation) {
            $requestParameterName = $setterHelper->getPropertyRequestParameter($violation->getPropertyPath());
            $validationErrors[$requestParameterName] = $violation->getMessage();
        }

        if(count($validationErrors) > 0){
            throw new RequestValidationException('Validation Error', $validationErrors);
        }

        $setterHelper->runPostValidationTasks();
    }

    public function persistEntity(object $entity) {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}