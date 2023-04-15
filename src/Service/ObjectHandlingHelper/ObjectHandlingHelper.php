<?php

namespace App\Service\ObjectHandlingHelper;

use App\Exceptions\InvalidObjectException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

class ObjectHandlingHelper{
    private ContainerInterface $container;

    public function __construct(Kernel $kernel)
    {
        $this->container = $kernel->getContainer();
    }

    public function getClassInstance(string $className, ?string $interface = null)
    {
        if(!class_exists($className)){
            throw new InvalidObjectException("{$className} class does not exist");
        }

        $instance = $this->container->get($className);
        
        if(!is_null($interface) && !($instance instanceof $interface)){
            throw new InvalidObjectException("{$className} class must implement $interface");
        }

        return $instance;
    } 
}