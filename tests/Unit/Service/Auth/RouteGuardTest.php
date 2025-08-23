<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use App\Entity\User;
use App\Exceptions\UnauthorizedException;
use App\Service\Auth\RouteGuard;
use App\Tests\Unit\Service\Auth\Stubs\ControllerStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class RouteGuardTest extends TestCase
{
    private MockObject&Security $securityMock;

    protected function setUp(): void
    {
        $this->securityMock = $this->createMock(Security::class);
    }

    public function testGetAuthorizedUserOrFailReturnsUser(): void
    {
        $userMock = $this->createMock(User::class);

        $this->securityMock->expects($this->once())
            ->method('getUser')
            ->willReturn($userMock);

        $routeGuard = new RouteGuard($this->securityMock);
        $user = $routeGuard->getAuthorizedUserOrFail();

        $this->assertInstanceOf(User::class, $user);
    }

    public function testGetAuthorizedUserOrFailThrowsExceptionForNotAuthorizedUser(): void
    {
        $this->securityMock->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $routeGuard = new RouteGuard($this->securityMock);
        $this->expectException(UnauthorizedException::class);
        $routeGuard->getAuthorizedUserOrFail();
    }

    public function testValidateAccessExecutesAccessRule(): void
    {
        $userMock = $this->createMock(User::class);
        $this->securityMock->expects($this->once())
            ->method('getUser')
            ->willReturn($userMock);

        $requestMock = $this->createMock(Request::class);
        $controller = new ControllerStub();

        $requestMock->expects($this->exactly(2))
        ->method('get')
        ->with('dummy');

        $routeGuard = new RouteGuard($this->securityMock);
        $routeGuard->validateAccess($controller, $requestMock, 'testMethod');
    }

}
