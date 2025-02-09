<?php

namespace App\EventListener;

use App\Response\UnauthorizedResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;

class AuthenticationFailureListener
{
    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $response = new UnauthorizedResponse('Invalid or expired token');
        $event->setResponse($response);
    }
}


