<?php

namespace App\Tests\Unit\Service\Auth\Stubs;

use App\Service\Auth\Attribute\RestrictedAccess;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[RestrictedAccess(accessRule: AccessRuleStub::class)]
class ControllerStub extends AbstractController
{
    #[RestrictedAccess(accessRule: AccessRuleStub::class)]
    public function testMethod() {}
}
