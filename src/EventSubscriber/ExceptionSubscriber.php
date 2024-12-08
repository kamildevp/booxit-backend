<?php

namespace App\EventSubscriber;

use App\Exceptions\ForbiddenException;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\UnauthorizedException;
use App\Kernel;
use App\Response\BadRequestResponse;
use App\Response\ForbiddenResponse;
use App\Response\Interface\ExceptionResponseInterface;
use App\Response\ServerErrorResponse;
use App\Response\UnauthorizedResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    private const EXCEPTION_RESPONSE_MAP = [
        'default' => ServerErrorResponse::class,
        UnauthorizedException::class => UnauthorizedResponse::class,
        ForbiddenException::class => ForbiddenResponse::class,
        InvalidRequestException::class => BadRequestResponse::class
    ];

    private string $environment;

    public function __construct(Kernel $kernel)
    {
        $this->environment = $kernel->getEnvironment();
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $throwableClass = get_class($throwable);
        $responseKey = array_key_exists($throwableClass, self::EXCEPTION_RESPONSE_MAP) ? $throwableClass : 'default';
        $responseClass = self::EXCEPTION_RESPONSE_MAP[$responseKey];
        
        if($this->environment != 'dev' && $responseKey == 'default'){
            $response = new $responseClass;
        }
        else {
            $response = is_a($responseClass, ExceptionResponseInterface::class, true) ?  $responseClass::createFromException($throwable) :  new $responseClass;
        }

        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

}
