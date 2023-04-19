<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\Organization;
use App\Entity\User;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

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
            throw new InvalidRequestException('Organization not found');
        }

        if(!($this->currentUser && $organization->hasMember($this->currentUser) && $organization->getMember($this->currentUser)->hasRoles(['ADMIN']))){
            throw new InvalidRequestException('Access Denied');
        }

        $this->object->setOrganization($organization);
    }

}