<?php

namespace App\Service\SetterHelper\Task\Schedule;

use App\Entity\Organization;
use App\Entity\User;
use App\Exceptions\AccessDeniedException;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/** @property Schedule $object */
class OrganizationTask implements SetterTaskInterface
{
    use SetterTaskTrait;
    private ?User $currentUser;

    public function __construct(private EntityManagerInterface $entityManager, Security $security)
    {
        $this->currentUser = $security->getUser();
    }

    public function runPreValidation(int $organizationId)
    {
        $organization = $this->entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            $this->requestErrors['organizationId'] = "Organization with id = {$organizationId} does not exist";
            return;
        }

        if(!($this->currentUser && $organization->hasMember($this->currentUser) && $organization->getMember($this->currentUser)->hasRoles(['ADMIN']))){
            throw new AccessDeniedException('Only organization admin can add schedule');
            return;
        }

        $this->object->setOrganization($organization);
    }

}