<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\Organization;
use App\Entity\Service;
use App\Entity\Schedule;
use App\Entity\User;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/** @property Schedule $object */
class ServicesTask implements SetterTaskInterface
{
    use SetterTaskTrait;
    private ?User $currentUser;
    const MODIFICATION_TYPES = ['ADD', 'REMOVE', 'OVERWRITE'];

    public function __construct(private EntityManagerInterface $entityManager, Security $security)
    {
        $this->currentUser = $security->getUser();
    }

    public function runPreValidation(array $services, string $servicesModificationType = 'ADD')
    {
        if(!in_array($servicesModificationType , self::MODIFICATION_TYPES)){
            $modificationTypesString = join(', ', self::MODIFICATION_TYPES);
            throw new InvalidRequestException("Invalid services modification type. Allowed modification types: {$modificationTypesString}");
        }

        $organization = $this->object->getOrganization();

        if(!($this->currentUser && $organization->hasMember($this->currentUser) && $organization->getMember($this->currentUser)->hasRoles(['ADMIN']))){
            throw new InvalidRequestException('Access Denied');
        }

        $this->assignServices($services, $organization, $servicesModificationType);
        
    }

    private function assignServices(array $services, Organization $organization, string $modificationType){
        if($modificationType === 'OVERWRITE'){
            $this->object->clearServices();
        }

        foreach($services as $serviceId){
            if(!is_int($serviceId)){
                throw new InvalidRequestException('Parameter services must be array of integers');
            }

            $service = $this->entityManager->getRepository(Service::class)->find($serviceId);
            if(!($service instanceof Service)){
                throw new InvalidRequestException("Service with id = {$serviceId} not found");
            }

            $serviceOrganization = $service->getOrganization();
            if($organization !== $serviceOrganization){
                throw new InvalidRequestException("Service with id = {$serviceId} does not belong to the same organization as modified schedule");
            }

            switch(true){
                case $modificationType === 'ADD' && $this->object->hasService($service):
                    throw new InvalidRequestException("Schedule already have service with id = {$serviceId}");
                    break;
                case $modificationType === 'ADD' || $modificationType === 'OVERWRITE':   
                    $this->object->addService($service);
                    break;
                case $modificationType === 'REMOVE' && !$this->object->hasService($service):
                    throw new InvalidRequestException("Schedule does not have service with id = {$serviceId}");
                    break;
                case $modificationType === 'REMOVE':
                    $this->object->removeService($service);
                    break;
            }
        }
    }



}