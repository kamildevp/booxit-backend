<?php

namespace App\Service\EntityHandler;

interface EntityHandlerInterface {
    public function parseParamsToEntity(
        object $entity, 
        array $requestParams, 
        array $requiredParameterGroups = [],
        array $allowedParameterGroups = ['Default']
    ): void;
}