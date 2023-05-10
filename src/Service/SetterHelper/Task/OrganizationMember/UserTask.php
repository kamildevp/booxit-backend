<?php

namespace App\Service\SetterHelper\Task\OrganizationMember;

use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\Task\SetterTaskInterface;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\ORM\EntityManagerInterface;

/** @property OrganizationMember $object */
class UserTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function __construct(private EntityManagerInterface $entityManager)
    {
        
    }

    public function runPreValidation(string $userId)
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if(!$user){
            $this->requestErrors['userId'] = "User with id = {$userId} does not exist";
            return;
        }

        $organization = $this->object->getOrganization();
        $member = $this->object;
        $memberExists = $organization->getMembers()->exists(function($key,$element) use ($user, $member){
            return $element != $member && $element->getAppUser() === $user;
        });

        if($memberExists){
            $this->requestErrors['userId'] = "User with id = {$userId} is already organization member";
            return;
        }

        $this->object->setAppUser($user);
    }



}