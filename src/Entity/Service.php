<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\Blameable;
use App\Entity\Trait\Timestampable;
use App\Enum\Service\ServiceNormalizerGroup;
use App\Repository\Filter\EntityFilter\DateIntervalFieldValue;
use App\Repository\Filter\EntityFilter\FieldContains;
use App\Repository\Filter\EntityFilter\FieldValue;
use App\Repository\Order\EntityOrder\BaseFieldOrder;
use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\SoftDeleteable as DoctrineSoftDeleteable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateIntervalNormalizer;

#[DoctrineSoftDeleteable]
#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    use Timestampable, Blameable, SoftDeleteableEntity;

    #[Groups([ServiceNormalizerGroup::BASE_INFO->value])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups([ServiceNormalizerGroup::ORGANIZATION->value])]
    #[ORM\ManyToOne(inversedBy: 'services')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

    #[Groups([ServiceNormalizerGroup::BASE_INFO->value])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups([ServiceNormalizerGroup::DETAILS->value])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Context([DateIntervalNormalizer::FORMAT_KEY => 'P%yY%mM%dDT%hH%iM'])]
    #[Groups([ServiceNormalizerGroup::DETAILS->value])]
    #[ORM\Column]
    private ?\DateInterval $duration = null;

    #[OA\Property(example: '25.50')]
    #[Groups([ServiceNormalizerGroup::DETAILS->value])]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
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

    public function getDuration(): ?\DateInterval
    {
        return $this->duration;
    }

    public function setDuration(\DateInterval $duration): self
    {
        $this->duration = $duration;

        return $this;
    }
    public function getEstimatedPrice(): ?string
    {
        return $this->estimatedPrice;
    }

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

    public static function getFilterDefs(): array
    {
        return array_merge(self::getTimestampsFilterDefs(), [
            'name' => new FieldContains('name'),
            'durationFrom' => new DateIntervalFieldValue('duration', '>='),
            'durationTo' => new DateIntervalFieldValue('duration', '<='),
            'estimatedPriceFrom' => new FieldValue('estimatedPrice', '>='),
            'estimatedPriceTo' => new FieldValue('estimatedPrice', '<='),
        ]);
    }

    public static function getOrderDefs(): array
    {
        return array_merge(self::getTimestampsOrderDefs(), [
            'id' => new BaseFieldOrder('id'),
            'name' => new BaseFieldOrder('name'),
            'duration' => new BaseFieldOrder('duration'),
            'estimated_price' => new BaseFieldOrder('estimatedPrice'),
        ]);
    }
}
