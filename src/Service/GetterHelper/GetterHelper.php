<?php

namespace App\Service\GetterHelper;

use App\Entity\User;
use App\Kernel;
use App\Service\GetterHelper\Attribute\Getter;
use App\Service\GetterHelper\CustomAccessRule\CustomAccessRuleInterface;
use App\Service\GetterHelper\CustomFormat\CustomFormatInterface;
use App\Service\GetterHelper\Util\GetterManager;
use App\Service\ObjectHandlingHelper\ObjectHandlingHelper;
use ReflectionClass;
use Symfony\Bundle\SecurityBundle\Security;

class GetterHelper implements GetterHelperInterface
{

    const GETTER_ATTRIBUTE = Getter::class;
    private array $getterMethods = [];
    private ?User $user;

    public function __construct(private Kernel $kernel, private Security $security)
    {
        $this->user = $this->security->getUser();
    }

    public function get(object $object, array $groups = ['Default']):array
    {
        $reflectionClass = new ReflectionClass($object);
        $getterManager = new GetterManager(self::GETTER_ATTRIBUTE, new ObjectHandlingHelper($this->kernel));
        $this->getterMethods = $getterManager->filterGetters($reflectionClass, $groups);

        $objectInfo = [];
        foreach($this->getterMethods as $getter){
            $getterName = $getter->getName();
            $targetPropertyAlias = $getter->getTargetPropertyAlias();
            $accessRule = $getter->getAccessRule();
            if(!(
                $accessRule === Getter::PUBLIC_ACCESS || 
                (($accessRule instanceof CustomAccessRuleInterface) && $accessRule->validateAccess($this->user, $object))
            ))
            {
                continue;
            }

            $property = $object->{$getterName}();
            $format = $getter->getFormat();

            if(!($format instanceof CustomFormatInterface)){
                $objectInfo[$targetPropertyAlias] =  $this->getPropertyValue($property, $groups);
                continue;
            }

            $objectInfo[$targetPropertyAlias] = $format->format($property);
        }
        return $objectInfo;
    }

    private function getPropertyValue($property, array $groups):mixed
    {
        if(!is_object($property) && !is_array($property)){
            return $property;
        }

        $getterHelper = new GetterHelper($this->kernel, $this->security);
        
        if(is_object($property) && !($property instanceof \Doctrine\Common\Collections\Collection)){
            return $getterHelper->get($property, $groups);
        }
        else{
            $values = [];
            foreach($property as $element){
                $values[] = $this->getPropertyValue($element, $groups);
            }
            return $values;
        }
    }
}