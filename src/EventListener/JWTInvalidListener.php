<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class JWTInvalidListener
{
    /**
     * @param JWTInvalidEvent $event
     */
    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $responseData = [
            'status' => 'fail',
            'data' => [
                'message' => 'Invalid JWT token'
            ]
        ];

        $response = new JsonResponse($responseData, 401);

        $event->setResponse($response);
    }
}


