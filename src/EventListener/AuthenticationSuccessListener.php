<?php

namespace App\EventListener;

use App\Service\GetterHelper\GetterHelperInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSuccessListener
{
    public function __construct(private GetterHelperInterface $getterHelper)
    {
        
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $data['user'] = $this->getterHelper->get($user, ['login']);

        $responseData = [
            'status' => 'success',
            'data' => $data
        ];

        $event->setData($responseData);
    }
}

