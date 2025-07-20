<?php

namespace App\Service\Auth\AccessRule;

use App\Entity\User;
use App\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticatedRule implements AccessRuleInterface
{
    public function validateAccess(?UserInterface $user, Request $request): void
    {
        if(!$user instanceof User){
            throw new UnauthorizedException;
        }
    }
}