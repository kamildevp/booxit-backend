<?php

namespace App\Entity;

use App\Repository\OrganizationRepository;
use App\Service\GetterHelper\Attribute\Getter;
use App\Service\SetterHelper\Attribute\Setter;
use App\Service\SetterHelper\Task\OrganizationMembersTask;
use App\Service\SetterHelper\Task\OrganizationServicesTask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
class Organization
{
    const ALLOWED_ROLES = ['MEMBER', 'ADMIN'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(
        min: 6,
        max: 50,
        minMessage: 'Minimum name length is 6 characters',
        maxMessage: 'Maximum name length is 50 characters'
    )]
    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: OrganizationMember::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $members;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Service::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $services;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Schedule::class, orphanRemoval: true)]
    private Collection $schedules;

    #[Assert\Length(
        max: 2000,
        maxMessage: 'Maximum description length is 2000 characters'
    )]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $banner = null;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->schedules = new ArrayCollection();
    }

    #[Getter(groups: ['schedule', 'user-organizations', 'organizations','reservation-organization'])]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Getter(groups:['schedule','user-organizations', 'organizations', 'organization', 'reservation-organization'])]
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

    #[Getter(groups: ['organization'])]
    public function getDescription(): ?string
    {
        return $this->description;
    }

    #[Setter]
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    #[Getter(groups: ['organizations', 'organization'])]
    public function getMembersCount(): ?string
    {
        return $this->members->count();
    }

    #[Getter(groups: ['organizations', 'organization'])]
    public function getServicesCount(): ?string
    {
        return $this->services->count();
    }

    #[Getter(groups: ['organizations', 'organization'])]
    public function getSchedulesCount(): ?string
    {
        return $this->schedules->count();
    }

    #[Getter(groups: ['organization-members'])]
    /**
     * @return Collection<int, OrganizationMember>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    #[Setter(setterTask: OrganizationMembersTask::class, groups: ['members'])]
    public function setMembers(Collection $members): self
    {
        $this->members = $members;

        return $this;
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

    #[Getter(groups: ['organization-services'])]
    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    #[Setter(setterTask: OrganizationServicesTask::class, groups: ['services'])]
    public function setServices(Collection $services): self
    {
        $this->services = $services;

        return $this;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setOrganization($this);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getOrganization() === $this) {
                $service->setOrganization(null);
            }
        }

        return $this;
    }

    #[Getter(groups: ['organization-schedules'])]
    /**
     * @return Collection<int, Schedule>
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function hasSchedule(Schedule $schedule):bool
    {
        $scheduleExists = $this->schedules->exists(function($key, $value) use ($schedule){
            return $value === $schedule;
        });
        return $scheduleExists;
    }

    public function addSchedule(Schedule $schedule): self
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
            $schedule->setOrganization($this);
        }

        return $this;
    }

    public function removeSchedule(Schedule $schedule): self
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getOrganization() === $this) {
                $schedule->setOrganization(null);
            }
        }

        return $this;
    }

    #[Getter(groups: ['organization-admins'])]
    /**
     * @return Collection<int, OrganizationMember>
     */
    public function getAdmins():Collection
    {
        return $this->members->filter(function($element){
            return $element->hasRoles(['ADMIN']);
        });
    }

    public function getBanner(): ?string
    {
        return $this->banner;
    }

    public function setBanner(?string $banner): self
    {
        $this->banner = $banner;

        return $this;
    }

}
