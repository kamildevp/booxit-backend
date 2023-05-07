<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use App\Service\GetterHelper\Attribute\Getter;
use App\Service\GetterHelper\CustomFormat\DateIntervalFormat;
use App\Service\SetterHelper\Attribute\Setter;
use App\Service\SetterHelper\Task\ServiceDurationTask;
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

    #[Assert\NotBlank]
    #[Assert\Length(
        min: 6,
        max: 50,
        minMessage: 'Minimum name length is 6 characters',
        maxMessage: 'Maximum name length is 50 characters'
    )]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Assert\Length(
        max: 255,
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

    #[ORM\OneToMany(mappedBy: 'service', targetEntity: Reservation::class)]
    private Collection $reservations;

    public function __construct()
    {
        $this->schedules = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    #[Getter(groups:['organization-services', 'schedule-services', 'reservation-service', 'schedule-reservations'])]
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    #[Getter]
    public function getOrganizationId(): int
    {
        return $this->organization->getId();
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    #[Getter(groups:['organization-services', 'schedule-services', 'reservation-service', 'schedule-reservations'])]
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

    #[Getter(groups: ['organization-services'])]
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

    #[Getter(format: DateIntervalFormat::class, groups: ['organization-services', 'schedule-services'])]
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

    #[Getter(groups:['organization-services', 'schedule-services', 'reservation-service', 'schedule-reservations'])]
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

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setService($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getService() === $this) {
                $reservation->setService(null);
            }
        }

        return $this;
    }

}
