<?php

namespace App\Service\SetterHelper\Task;

use Doctrine\Common\Collections\Collection;

/**
 * Interface SetterTaskInterface
 * @use App\Service\SetterHelper\Trait\SetterTaskTrait
 * @property object object
 */
interface SetterTaskInterface
{
    /** @return Collection<int, TaskParameter> */
    public function getTaskParameters():Collection;

    public function runPreValidationTask(array $params):void;

    public function runPostValidationTask(array $params):void;

    public function setObject(object $object):void;

    public function getValidationGroups():array;

    public function getValidationErrors():array;

    public function getRequestErrors():array;
}