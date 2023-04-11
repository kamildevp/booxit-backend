<?php

namespace App\Entity;

use App\Repository\OrganizationMemberRepository;
use App\Service\SetterHelper\Attribute\Setter;
use App\Service\SetterHelper\Task\MemberRoleTask;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrganizationMemberRepository::class)]
class OrganizationMember
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'organizationMembers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

    #[ORM\ManyToOne(inversedBy: 'organizationAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $appUser = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getAppUser(): ?User
    {
        return $this->appUser;
    }

    public function setAppUser(?User $appUser): self
    {
        $this->appUser = $appUser;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    #[Setter(setterTask: MemberRoleTask::class)]
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function hasRoles(array $roles):bool
    {
        return empty(array_diff($roles, $this->roles));
    }
}
