<?php

namespace App\Service\GetterHelper;

use Doctrine\Common\Collections\Collection;

interface GetterHelperInterface{

    public function get(object $object, array $groups = ['Default'], ?string $collectionRange = null):array;

    public function getCollection(Collection|array $collection, array $groups = ['Default'], ?string $collectionRange = null);
}