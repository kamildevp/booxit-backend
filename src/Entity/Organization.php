<?php

namespace App\Entity;

use App\Entity\Trait\Blameable;
use App\Entity\Trait\Timestampable;
use App\Enum\Organization\OrganizationNormalizerGroup;
use App\Repository\Filter\EntityFilter\FieldContains;
use App\Repository\Order\EntityOrder\BaseFieldOrder;
use App\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
class Organization
{
    use Timestampable, Blameable, SoftDeleteableEntity;

    #[Groups([OrganizationNormalizerGroup::BASE_INFO->value])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups([OrganizationNormalizerGroup::BASE_INFO->value])]
    #[ORM\Column(length: 50, unique: true)]
    private ?string $name = null;

    #[Groups([OrganizationNormalizerGroup::DETAILS->value])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToOne(cascade: ['remove'])]
    private ?File $bannerFile = null;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: OrganizationMember::class, fetch: 'EXTRA_LAZY')]
    private Collection $members;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Service::class, fetch: 'EXTRA_LAZY')]
    private Collection $services;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Schedule::class, fetch: 'EXTRA_LAZY')]
    private Collection $schedules;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Reservation::class)]
    private Collection $reservations;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMembersCount(): ?string
    {
        return $this->members->count();
    }

    public function getServicesCount(): ?string
    {
        return $this->services->count();
    }

    public function getSchedulesCount(): ?string
    {
        return $this->schedules->count();
    }

    /**
     * @return Collection<int, OrganizationMember>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

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

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

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

    public function getBannerFile(): ?File
    {
        return $this->bannerFile;
    }

    public function setBannerFile(?File $bannerFile): static
    {
        $this->bannerFile = $bannerFile;
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
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setOrganization($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getOrganization() === $this) {
                $reservation->setOrganization(null);
            }
        }

        return $this;
    }
}
