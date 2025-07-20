<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Exceptions\UnauthorizedException;
use App\Service\Auth\AccessRule\AccessRuleInterface;
use App\Service\Auth\Attribute\RestrictedAccess;
use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class RouteGuard implements RouteGuardInterface
{
    private ?UserInterface $user = null;

    public function __construct(
        private Security $security,
    )
    {
        $this->user = $this->security->getUser();
    }

    public function validateAccess(AbstractController $controller, Request $request, ?string $methodName = null): void
    {
        $this->validateLocationAccess($request, $controller);
        $this->validateLocationAccess($request, $controller, $methodName ?? '_invoke');
    }

    public function getAuthorizedUserOrFail(): User
    {
        if(!($this->user instanceof User)){
            throw new UnauthorizedException;
        }

        return $this->user;
    }

    private function validateLocationAccess(Request $request, AbstractController $controller, ?string $methodName = null): void
    {
        $restrictedAccessAttributes = $this->getRestrictedAccessAttributes($controller, $methodName);

        foreach($restrictedAccessAttributes as $attribute){
            $accessRule = $this->resolveAccessRule($attribute);
            $accessRule->validateAccess($this->user, $request);
        }
    }

    private function getRestrictedAccessAttributes(AbstractController $controller, ?string $methodName = null): array
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
        $accessRule = new $accessRuleClass;
        if(!($accessRule instanceof AccessRuleInterface)){
            throw new InvalidArgumentException('Access Rule must implement AccessRuleInterface');
        }

        return $accessRule;
    }
}