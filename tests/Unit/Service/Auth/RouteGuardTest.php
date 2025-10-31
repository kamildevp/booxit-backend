<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use App\Entity\User;
use App\Exceptions\UnauthorizedException;
use App\Kernel;
use App\Service\Auth\RouteGuard;
use App\Tests\Unit\Service\Auth\Stubs\AccessRuleStub;
use App\Tests\Unit\Service\Auth\Stubs\ControllerStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class RouteGuardTest extends TestCase
{
    private MockObject&Security $securityMock;
    private MockObject&Kernel $kernelMock;

    protected function setUp(): void
    {
        $this->securityMock = $this->createMock(Security::class);
        $this->kernelMock = $this->createMock(Kernel::class);
    }

    public function testGetAuthorizedUserOrFailReturnsUser(): void
    {
        $userMock = $this->createMock(User::class);

        $this->securityMock->expects($this->once())
            ->method('getUser')
            ->willReturn($userMock);

        $routeGuard = new RouteGuard($this->securityMock, $this->kernelMock);
        $user = $routeGuard->getAuthorizedUserOrFail();

        $this->assertInstanceOf(User::class, $user);
    }

    public function testGetAuthorizedUserOrFailThrowsExceptionForNotAuthorizedUser(): void
    {
        $this->securityMock->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $routeGuard = new RouteGuard($this->securityMock, $this->kernelMock);
        $this->expectException(UnauthorizedException::class);
        $routeGuard->getAuthorizedUserOrFail();
    }

    public function testValidateAccessExecutesAccessRule(): void
    {
        $userMock = $this->createMock(User::class);
        $containerMock = $this->createMock(ContainerInterface::class);
        $this->securityMock->method('getUser')->willReturn($userMock);
        $this->kernelMock->method('getContainer')->willReturn($containerMock);
        $containerMock->method('get')->with(AccessRuleStub::class)->willReturn(new AccessRuleStub());

        $requestMock = $this->createMock(Request::class);
        $controller = new ControllerStub();

        $requestMock->expects($this->exactly(2))
        ->method('get')
        ->with('dummy');

        $routeGuard = new RouteGuard($this->securityMock, $this->kernelMock);
        $routeGuard->validateAccess($controller, $requestMock, 'testMethod');
    }

}
