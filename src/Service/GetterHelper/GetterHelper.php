<?php

namespace App\Service\GetterHelper;

use App\Entity\User;
use App\Kernel;
use App\Service\GetterHelper\Attribute\Getter;
use App\Service\GetterHelper\CustomAccessRule\CustomAccessRuleInterface;
use App\Service\GetterHelper\Util\GetterManager;
use ReflectionClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GetterHelper implements GetterHelperInterface
{

    const GETTER_ATTRIBUTE = Getter::class;
    private ContainerInterface $container;
    private array $getterMethods = [];
    private ?User $user;

    public function __construct(Kernel $kernel, Security $security)
    {
        $this->container = $kernel->getContainer();
        $this->user = $security->getUser();
    }

    public function get(object $object):array
    {
        $reflectionClass = new ReflectionClass($object);
        $getterManager = new GetterManager(self::GETTER_ATTRIBUTE, $this->container);
        $this->getterMethods = $getterManager->filterGetters($reflectionClass);

        $objectInfo = [];
        foreach($this->getterMethods as $getter){
            $getterName = $getter->getName();
            $targetProperty = $getter->getTargetProperty();
            $accessRule = $getter->getAccessRule();
            if(
                $accessRule === Getter::PUBLIC_ACCESS || 
                (($accessRule instanceof CustomAccessRuleInterface) && $accessRule->validateAccess($this->user, $object))
            )
            {
                    $objectInfo[$targetProperty] = $object->{$getterName}();
            }
        }
        return $objectInfo;
    }
}