<?php

namespace App\EventListener;

use App\Response\UnauthorizedResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class JWTExpiredListener
{
    public function onJWTExpired(JWTExpiredEvent $event)
    {
        $response = new UnauthorizedResponse('Expired JWT token');
        $event->setResponse($response);
    }
}


