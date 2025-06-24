<?php

namespace App\EventListener;

use App\Response\UnauthorizedResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;

class JWTInvalidListener
{
    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $response = new UnauthorizedResponse('Invalid JWT token');
        $event->setResponse($response);
    }
}


