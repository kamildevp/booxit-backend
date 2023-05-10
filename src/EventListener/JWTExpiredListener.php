<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class JWTExpiredListener
{
    /**
     * @param JWTExpiredEvent $event
     */
    public function onJWTExpired(JWTExpiredEvent $event)
    {
        $responseData = [
            'status' => 'fail',
            'data' => [
                'message' => 'Expired JWT token'
            ]
        ];

        $response = new JsonResponse($responseData, 401);

        $event->setResponse($response);
    }
}


