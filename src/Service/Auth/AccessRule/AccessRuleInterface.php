<?php

namespace App\Service\Auth\AccessRule;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

interface AccessRuleInterface
{
    public function validateAccess(?UserInterface $user, Request $request): void;
}