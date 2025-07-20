<?php

namespace App\EventSubscriber;

use App\Service\Auth\RouteGuardInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class ControllerSubscriber implements EventSubscriberInterface
{

    public function __construct(private RouteGuardInterface $routeGuard)
    {
        
    }


    public function onController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        if (is_array($controller) || is_object($controller)) {
            [$controllerInstance, $methodName] = is_array($controller) ? $controller : [$controller, null];
            $this->routeGuard->validateAccess($controllerInstance, $request, $methodName);
        }

    }
    public static function getSubscribedEvents(): array
    {
        return [
            ControllerEvent::class => 'onController',
        ];
    }

}
