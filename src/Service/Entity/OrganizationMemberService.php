<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\DTO\OrganizationMember\OrganizationMemberPatchDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\EntitySerializer\EntitySerializerInterface;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Enum\Organization\OrganizationRole;
use App\Exceptions\ConflictException;
use App\Repository\OrganizationMemberRepository;

class OrganizationMemberService
{
    public function __construct(
        protected EntitySerializerInterface $entitySerializer,
        protected OrganizationMemberRepository $organizationMemberRepository,
        protected UserRepository $userRepository,
    )
    {

    }

    public function createOrganizationMember(Organization $organization, User|int $user, OrganizationRole $role): OrganizationMember
    {
        $user = $user instanceof User ? $user : $this->userRepository->findOrFail($user);
        $existingOrganizationMember = $this->organizationMemberRepository->findOneBy([
            'organization' => $organization,
            'appUser' => $user
        ]);

        if($existingOrganizationMember){
            return $existingOrganizationMember;
        }

        $organizationMember = new OrganizationMember();
        $organizationMember->setOrganization($organization);
        $organizationMember->setAppUser($user);
        $organizationMember->setRole($role->value);
        $this->organizationMemberRepository->save($organizationMember, true);

        return $organizationMember;
    }

    public function patchOrganizationMember(OrganizationMember $organizationMember, OrganizationMemberPatchDTO $dto): OrganizationMember
    {
        $organizationMemberRole = $organizationMember->getRole();
        if($organizationMemberRole == OrganizationRole::ADMIN->value && $organizationMemberRole != $dto->role){
            $organization = $organizationMember->getOrganization();
            $adminsCount = $this->organizationMemberRepository->getOrganizationMembersCount($organization->getId(), OrganizationRole::ADMIN);
            if($adminsCount == 1){
                throw new ConflictException('The admin role cannot be removed because this user is the only administrator of the organization.');
            }
        }

        $organizationMember = $this->entitySerializer->parseToEntity($dto, $organizationMember);
        $this->organizationMemberRepository->save($organizationMember, true);

        return $organizationMember;
    }

    public function removeOrganizationMember(OrganizationMember $organizationMember): void
    {
        if($organizationMember->getRole() == OrganizationRole::ADMIN->value){
            $organization = $organizationMember->getOrganization();
            $adminsCount = $this->organizationMemberRepository->getOrganizationMembersCount($organization->getId(), OrganizationRole::ADMIN);
            if($adminsCount == 1){
                throw new ConflictException('Cannot remove the only administrator of organization.');
            }
        }

        $this->organizationMemberRepository->remove($organizationMember, true);
    }
}