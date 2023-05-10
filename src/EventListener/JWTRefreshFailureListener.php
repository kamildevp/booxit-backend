<?php

namespace App\EventListener;

use Gesdinet\JWTRefreshTokenBundle\Event\RefreshAuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class JWTRefreshFailureListener
{
    /**
     * @param RefreshAuthenticationFailureEvent $event
     */
    public function onJWTRefreshFailure(RefreshAuthenticationFailureEvent $event)
    {
        $responseData = [
            'status' => 'fail',
            'data' => [
                'message' => 'Invalid refresh token'
            ]
        ];

        $response = new JsonResponse($responseData, 401);

        $event->setResponse($response);
    }
}


