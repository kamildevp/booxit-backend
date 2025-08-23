<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Response\UnauthorizedResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;

class JWTExpiredListener
{
    public function onJWTExpired(JWTExpiredEvent $event)
    {
        $response = new UnauthorizedResponse('Expired JWT token');
        $event->setResponse($response);
    }
}


