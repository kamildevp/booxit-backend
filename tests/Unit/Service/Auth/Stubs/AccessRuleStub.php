<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth\Stubs;

use App\Service\Auth\AccessRule\AccessRuleInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessRuleStub implements AccessRuleInterface
{
    public function validateAccess(?UserInterface $user, Request $request, array $controllerArguments): void
    {
        $request->get('dummy');
    }
}
