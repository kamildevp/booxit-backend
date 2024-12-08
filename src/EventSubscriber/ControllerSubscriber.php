<?php

namespace App\EventSubscriber;

use App\Service\Auth\AuthServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class ControllerSubscriber implements EventSubscriberInterface
{

    public function __construct(private AuthServiceInterface $authService)
    {
        
    }


    public function onController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        if (is_array($controller) || is_object($controller)) {
            [$controllerObject, $methodName] = is_array($controller) ? $controller : [$controller, '__invoke'];
            $this->authService->validateAccess($controllerObject, $methodName, $request);
        }

    }
    public static function getSubscribedEvents(): array
    {
        return [
            ControllerEvent::class => 'onController',
        ];
    }

}
