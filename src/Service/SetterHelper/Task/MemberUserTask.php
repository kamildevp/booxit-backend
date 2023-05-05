<?php

namespace App\Service\SetterHelper\Task;

use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Exceptions\InvalidRequestException;
use App\Service\SetterHelper\Trait\SetterTaskTrait;
use Doctrine\ORM\EntityManagerInterface;

/** @property OrganizationMember $object */
class MemberUserTask implements SetterTaskInterface
{
    use SetterTaskTrait;

    public function __construct(private EntityManagerInterface $entityManager)
    {
        
    }

    public function runPreValidation(string $userId)
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if(!$user){
            throw new InvalidRequestException("User with id = {$userId} not found");
        }

        $organization = $this->object->getOrganization();
        if(!$organization){
            return;
        }

        $member = $this->object;
        $memberExists = $organization->getMembers()->exists(function($key,$element) use ($user, $member){
            return $element != $member && $element->getAppUser() === $user;
        });

        if($memberExists){
            throw new InvalidRequestException("User with id = {$userId} is already organization member");
        }

        $this->object->setAppUser($user);
    }



}