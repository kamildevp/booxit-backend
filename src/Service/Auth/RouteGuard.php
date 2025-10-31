<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Entity\User;
use App\Exceptions\UnauthorizedException;
use App\Kernel;
use App\Service\Auth\AccessRule\AccessRuleInterface;
use App\Service\Auth\Attribute\RestrictedAccess;
use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class RouteGuard implements RouteGuardInterface
{
    private ?UserInterface $user = null;

    public function __construct(
        protected Security $security,
        protected Kernel $kernel,
    )
    {
        $this->user = $this->security->getUser();
    }

    public function validateAccess(
        mixed $controller, 
        Request $request, 
        ?string $methodName = null,
    ): void
    {
        $this->validateLocationAccess($request, $controller);
        $this->validateLocationAccess($request, $controller, $methodName ?? '__invoke');
    }

    public function getAuthorizedUserOrFail(): User
    {
        if(!($this->user instanceof User)){
            throw new UnauthorizedException;
        }

        return $this->user;
    }

    private function validateLocationAccess(
        Request $request, 
        mixed $controller, 
        ?string $methodName = null
    ): void
    {
        $restrictedAccessAttributes = $this->getRestrictedAccessAttributes($controller, $methodName);

        foreach($restrictedAccessAttributes as $attribute){
            $accessRule = $this->resolveAccessRule($attribute);
            $accessRule->validateAccess($this->user, $request);
        }
    }

    private function getRestrictedAccessAttributes(mixed $controller, ?string $methodName = null): array
    {
        $reflection = $methodName ? new ReflectionMethod($controller, $methodName) : new ReflectionClass($controller);
        return $reflection->getAttributes(RestrictedAccess::class);
    }

    /**
     * @param ReflectionAttribute<RestrictedAccess> $attribute
     */
    private function resolveAccessRule(ReflectionAttribute $attribute): AccessRuleInterface
    {
        $accessRuleClass = $attribute->newInstance()->accessRule;
        $accessRule = $this->kernel->getContainer()->get($accessRuleClass);
        if(!($accessRule instanceof AccessRuleInterface)){
            throw new InvalidArgumentException('Access Rule must implement AccessRuleInterface');
        }

        return $accessRule;
    }
}