<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthenticationFailureListener
{
    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $responseData = [
            'status' => 'fail',
            'data' => [
                'message' => 'Invalid credentials'
            ]
        ];

        $response = new JsonResponse($responseData, 401);

        $event->setResponse($response);
    }
}


