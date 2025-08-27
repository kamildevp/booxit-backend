<?php

declare(strict_types=1);

namespace App\Service\Auth\AccessRule;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

interface AccessRuleInterface
{
    public function validateAccess(?UserInterface $user, Request $request, array $controllerArguments): void;
}