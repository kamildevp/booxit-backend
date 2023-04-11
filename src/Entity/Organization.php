<?php

namespace App\Entity;

use App\Repository\OrganizationRepository;
use App\Service\GetterHelper\Attribute\Getter;
use App\Service\SetterHelper\Attribute\Setter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
class Organization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^(?!\s)[^<>]{6,40}$/i',
        message: 'Name must be from 6 to 40 characters long, cannot start from whitespace and contain characters: <>'
    )]
    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: OrganizationMember::class, orphanRemoval: true)]
    private Collection $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Getter]
    public function getName(): ?string
    {
        return $this->name;
    }

    #[Setter]
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, OrganizationMember>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(OrganizationMember $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->setOrganization($this);
        }

        return $this;
    }

    public function removeMember(OrganizationMember $member): self
    {
        if ($this->members->removeElement($member)) {
            // set the owning side to null (unless already changed)
            if ($member->getOrganization() === $this) {
                $member->setOrganization(null);
            }
        }

        return $this;
    }

    public function hasMember(User $user):bool
    {
        $memberExists = $this->members->exists(function($key, $value) use ($user){
            return $value->getAppUser() === $user;
        });
        return $memberExists;
    }

    public function getMember(User $user):?OrganizationMember
    {
        $member = $this->members->findFirst(function($key, $value) use ($user){
            return $value->getAppUser() === $user;
        });
        return $member;
    }
}
