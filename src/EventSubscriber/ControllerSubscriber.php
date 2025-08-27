<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\Auth\RouteGuardInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

class ControllerSubscriber implements EventSubscriberInterface
{

    public function __construct(private RouteGuardInterface $routeGuard)
    {
        
    }


    public function onControllerArguments(ControllerArgumentsEvent $event): void
    {
        $controller = $event->getController();
        $request = $event->getRequest();
        $controllerArguments = $event->getArguments();

        if (is_array($controller) || is_object($controller)) {
            [$controllerInstance, $methodName] = is_array($controller) ? $controller : [$controller, null];
            $this->routeGuard->validateAccess($controllerInstance, $request, $controllerArguments, $methodName);
        }

    }
    public static function getSubscribedEvents(): array
    {
        return [
            ControllerArgumentsEvent::class => 'onControllerArguments',
        ];
    }

}
