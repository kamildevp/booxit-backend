<?php

namespace App\Service\GetterHelper;

interface GetterHelperInterface{

    public function get(object $object, array $groups = ['Default']):array;
}