<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use App\Service\GetterHelper\Attribute\Getter;
use App\Service\GetterHelper\CustomFormat\DateIntervalFormat;
use App\Service\SetterHelper\Attribute\Setter;
use App\Service\SetterHelper\Task\ServiceDurationTask;
use App\Service\SetterHelper\Task\OrganizationTask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'services')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

    #[Assert\Regex(
        pattern: '/^(?!\s)[^<>]{6,40}$/i',
        message: 'Name must be from 6 to 40 characters long, cannot start from whitespace and contain characters: <>'
    )]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Assert\Length(
        max: 180,
        maxMessage: 'Max length of description is 255 characters'
    )]
    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateInterval $duration = null;

    #[Assert\Length(
        max: 10,
        maxMessage: 'Max length of estimated price is 10 characters'
    )]
    #[ORM\Column(length: 255)]
    private ?string $estimatedPrice = null;

    #[ORM\ManyToMany(targetEntity: Schedule::class, mappedBy: 'services')]
    private Collection $schedules;

    public function __construct()
    {
        $this->schedules = new ArrayCollection();
    }

    #[Getter(groups:['schedule-services'])]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Getter(groups: ['organizationDetails'])]
    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    #[Getter]
    public function getOrganizationId(): int
    {
        return $this->organization->getId();
    }

    #[Setter(targetParameter: 'organization_id', setterTask: OrganizationTask::class, groups: ['initOnly'])]
    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    #[Getter(groups:['schedule-services'])]
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

    #[Getter]
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

    #[Getter(format: DateIntervalFormat::class, groups: ['schedule-services'])]
    public function getDuration(): ?\DateInterval
    {
        return $this->duration;
    }

    #[Setter(setterTask: ServiceDurationTask::class)]
    public function setDuration(\DateInterval $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    #[Getter(groups:['schedule-services'])]
    public function getEstimatedPrice(): ?string
    {
        return $this->estimatedPrice;
    }

    #[Setter]
    public function setEstimatedPrice(string $estimatedPrice): self
    {
        $this->estimatedPrice = $estimatedPrice;

        return $this;
    }

    /**
     * @return Collection<int, Schedule>
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function addSchedule(Schedule $schedule): self
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
            $schedule->addService($this);
        }

        return $this;
    }

    public function removeSchedule(Schedule $schedule): self
    {
        if ($this->schedules->removeElement($schedule)) {
            $schedule->removeService($this);
        }

        return $this;
    }

}
