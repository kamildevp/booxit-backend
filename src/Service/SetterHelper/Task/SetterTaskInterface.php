<?php

namespace App\Service\SetterHelper\Task;

use App\Service\SetterHelper\Model\ParameterContainer;

/**
 * Interface SetterTaskInterface
 * @use SetterTrait
 * @property object object
 * @method runPreValidation
 * @method runPostValidation
 */
interface SetterTaskInterface
{
    public function getTaskParameters():ParameterContainer;

    public function runPreValidationTask(array $params):void;

    public function runPostValidationTask(array $params):void;

    public function setObject(object $object):void;

    public function getValidationGroups():array;

}