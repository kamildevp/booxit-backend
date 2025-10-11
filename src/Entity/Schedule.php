<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Trait\Blameable;
use App\Entity\Trait\Timestampable;
use App\Enum\Schedule\ScheduleNormalizerGroup;
use App\Repository\Filter\EntityFilter\FieldContains;
use App\Repository\Order\EntityOrder\BaseFieldOrder;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
class Schedule
{
    use Timestampable, Blameable, SoftDeleteableEntity;

    #[Groups([ScheduleNormalizerGroup::BASE_INFO->value])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups([ScheduleNormalizerGroup::ORGANIZATION->value])]
    #[ORM\ManyToOne(inversedBy: 'schedules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

    #[Groups([ScheduleNormalizerGroup::BASE_INFO->value])]
    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[Groups([ScheduleNormalizerGroup::DETAILS->value])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Service::class, inversedBy: 'schedules', fetch: 'EXTRA_LAZY')]
    private Collection $services;

    #[ORM\OneToMany(mappedBy: 'schedule', targetEntity: ScheduleAssignment::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $assignments;

    #[ORM\OneToMany(mappedBy: 'schedule', targetEntity: Reservation::class, orphanRemoval: true)]
    private Collection $reservations;

    #[ORM\OneToMany(mappedBy: 'schedule', targetEntity: WeekdayTimeWindow::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $weekdayTimeWindows;

    #[ORM\OneToMany(mappedBy: 'schedule', targetEntity: CustomTimeWindow::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $customTimeWindows;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->assignments = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->weekdayTimeWindows = new ArrayCollection();
        $this->customTimeWindows = new ArrayCollection();
    }

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function setServices(Collection $services)
    {
        $this->services = $services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        $this->services->removeElement($service);

        return $this;
    }

    public function hasService(Service $service): bool
    {
        return $this->services->contains($service);
    }

    /**
     * @return Collection<int, ScheduleAssignment>
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function addAssignment(ScheduleAssignment $assignment): self
    {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setSchedule($this);
        }

        return $this;
    }

    public function removeAssignment(ScheduleAssignment $assignment): self
    {
        if ($this->assignments->removeElement($assignment)) {
            // set the owning side to null (unless already changed)
            if ($assignment->getSchedule() === $this) {
                $assignment->setSchedule(null);
            }
        }

        return $this;
    }

    public function setAssignments(Collection $assignments): self
    {
        $this->assignments = $assignments;
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
            $reservation->setSchedule($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getSchedule() === $this) {
                $reservation->setSchedule(null);
            }
        }

        return $this;
    }

    public static function getFilterDefs(): array
    {
        return array_merge(self::getTimestampsFilterDefs(), [
            'name' => new FieldContains('name'),
        ]);
    }

    public static function getOrderDefs(): array
    {
        return array_merge(self::getTimestampsOrderDefs(), [
            'id' => new BaseFieldOrder('id'),
            'name' => new BaseFieldOrder('name'),
        ]);
    }

    /**
     * @return Collection<int, WeekdayTimeWindow>
     */
    public function getWeekdayTimeWindows(): Collection
    {
        return $this->weekdayTimeWindows;
    }

    public function addWeekdayTimeWindow(WeekdayTimeWindow $weekdayWorkingHour): static
    {
        if (!$this->weekdayTimeWindows->contains($weekdayWorkingHour)) {
            $this->weekdayTimeWindows->add($weekdayWorkingHour);
            $weekdayWorkingHour->setSchedule($this);
        }

        return $this;
    }

    public function removeWeekdayTimeWindow(WeekdayTimeWindow $weekdayWorkingHour): static
    {
        if ($this->weekdayTimeWindows->removeElement($weekdayWorkingHour)) {
            // set the owning side to null (unless already changed)
            if ($weekdayWorkingHour->getSchedule() === $this) {
                $weekdayWorkingHour->setSchedule(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CustomTimeWindow>
     */
    public function getCustomTimeWindows(): Collection
    {
        return $this->customTimeWindows;
    }

    public function addCustomTimeWindow(CustomTimeWindow $customTimeWindow): static
    {
        if (!$this->customTimeWindows->contains($customTimeWindow)) {
            $this->customTimeWindows->add($customTimeWindow);
            $customTimeWindow->setSchedule($this);
        }

        return $this;
    }

    public function removeCustomTimeWindow(CustomTimeWindow $customTimeWindow): static
    {
        if ($this->customTimeWindows->removeElement($customTimeWindow)) {
            // set the owning side to null (unless already changed)
            if ($customTimeWindow->getSchedule() === $this) {
                $customTimeWindow->setSchedule(null);
            }
        }

        return $this;
    }
}
