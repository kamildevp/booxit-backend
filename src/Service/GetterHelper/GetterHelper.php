<?php

namespace App\Service\GetterHelper;

use App\Entity\User;
use App\Exceptions\InvalidRequestException;
use App\Kernel;
use App\Service\GetterHelper\Attribute\Getter;
use App\Service\GetterHelper\CustomAccessRule\CustomAccessRuleInterface;
use App\Service\GetterHelper\CustomFormat\CustomFormatInterface;
use App\Service\GetterHelper\Util\GetterManager;
use App\Service\ObjectHandlingHelper\ObjectHandlingHelper;
use Doctrine\Common\Collections\Collection;
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

    public function getCollection(Collection|array $collection, array $groups = ['Default'], ?string $collectionRange = null){
        $range = !is_null($collectionRange) ? $this->getCollectionRange($collectionRange) : null;
        return $this->getPropertyValue($collection, $groups, $range);
    }

    public function get(object $object, array $groups = ['Default'], ?string $collectionRange = null):array
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
            $range = !is_null($collectionRange) ? $this->getCollectionRange($collectionRange) : null;

            if(!($format instanceof CustomFormatInterface)){
                $objectInfo[$targetPropertyAlias] =  $this->getPropertyValue($property, $groups, $range);
                continue;
            }

            $objectInfo[$targetPropertyAlias] = $format->format($property);
        }
        return $objectInfo;
    }

    private function getPropertyValue($property, array $groups, ?array $collectionRange = null):mixed
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
            $property = ($property instanceof \Doctrine\Common\Collections\Collection) ? $property->getValues() : $property;
            $elementsCount = count($property);
            
            $loopStart = isset($collectionRange['start']) ? $collectionRange['start']-1 : 0;
            $loopEnd = (isset($collectionRange['end']) && $collectionRange['end'] < $elementsCount) ? $collectionRange['end'] : $elementsCount;
            for($i = $loopStart; $i < $loopEnd; $i++){
                $element = $property[$i];
                $values[] = $this->getPropertyValue($element, $groups);
            }
            return $values;
        }
    }

    private function getCollectionRange(string $stringRange){
        $rangeSplit = explode('-', $stringRange);
        if(count($rangeSplit) != 2){
            throw new InvalidRequestException("Range must be in {start}-{end} format");
        }
        
        if(!(ctype_digit($rangeSplit[0]) &&  (int)$rangeSplit[0] > 0) || !(ctype_digit($rangeSplit[1]) && (int)$rangeSplit[1] > 0)){
            throw new InvalidRequestException("Range start and end must be postive integers");
        }

        $range['start'] = (int)$rangeSplit[0];
        $range['end'] = (int)$rangeSplit[1];

        if($range['start'] > $range['end']){
            throw new InvalidRequestException("Range start must be less or equal than range end");
        }

        return $range;
    }
}