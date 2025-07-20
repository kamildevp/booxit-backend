<?php

namespace App\Tests\Unit\Service\Auth\Stubs;

use App\Service\Auth\AccessRule\AccessRuleInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessRuleStub implements AccessRuleInterface
{
    public function validateAccess(?UserInterface $user, Request $request): void
    {
        $request->get('dummy');
    }
}
