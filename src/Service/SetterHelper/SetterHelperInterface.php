<?php

namespace App\Service\SetterHelper;


/**
 * Interface SetterHelperInterface
 * @throws InvalidRequestException when provided settings are invalid
 * @throws InvalidObjectException when processed object is configured incorrectly
 */
interface SetterHelperInterface
{
    public function updateObjectSettings(object $object, array $settings, array $requiredGroups = [], array $allowedGroups = ['Default']):void;

    public function runPostValidationTasks():void;

    public function getValidationGroups():array;

    public function getValidationErrors():array;

    public function getPropertyRequestParameter(string $propertyName):string;

    public function getRequestErrors():array;

    public function newInstance(): self;
}