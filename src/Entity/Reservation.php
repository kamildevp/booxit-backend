<?php

namespace App\Entity;

use App\Entity\Trait\Blameable;
use App\Entity\Trait\Timestampable;
use App\Enum\Reservation\ReservationNormalizerGroup;
use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    use Timestampable, Blameable, SoftDeleteableEntity;

    #[Groups([ReservationNormalizerGroup::BASE_INFO->value])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups([ReservationNormalizerGroup::BASE_INFO->value])]
    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[Groups([ReservationNormalizerGroup::SCHEDULE->value])]
    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Schedule $schedule = null;

    #[Groups([ReservationNormalizerGroup::SENSITIVE->value])]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[Groups([ReservationNormalizerGroup::SENSITIVE->value])]
    #[ORM\Column(length: 255)]
    private ?string $phoneNumber = null;

    #[Groups([ReservationNormalizerGroup::SERVICE->value])]
    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Service $service = null;

    #[Groups([ReservationNormalizerGroup::ORGANIZATION_ONLY->value])]
    #[ORM\Column]
    private ?bool $verified = null;

    #[Groups([ReservationNormalizerGroup::ORGANIZATION_ONLY->value])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expiryDate = null;

    #[Groups([ReservationNormalizerGroup::DETAILS->value])]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $estimatedPrice = null;

    #[Groups([ReservationNormalizerGroup::DETAILS->value])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startDateTime = null;

    #[Groups([ReservationNormalizerGroup::DETAILS->value])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endDateTime = null;

    #[Groups([ReservationNormalizerGroup::ORGANIZATION_ONLY->value])]
    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[Groups([ReservationNormalizerGroup::DETAILS->value])]
    #[ORM\Column(length: 255)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganization(): ?Organization
    {
        return $this->schedule->getOrganization();
    }

    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(?Schedule $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): self
    {
        $this->verified = $verified;

        return $this;
    }

    public function getExpiryDate(): ?\DateTimeInterface
    {
        return $this->expiryDate;
    }

    public function setExpiryDate(?\DateTimeInterface $expiryDate): self
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getEstimatedPrice(): ?string
    {
        return $this->estimatedPrice;
    }

    public function setEstimatedPrice(string $estimatedPrice): static
    {
        $this->estimatedPrice = $estimatedPrice;

        return $this;
    }

    public function getStartDateTime(): ?\DateTimeInterface
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(\DateTimeInterface $startDateTime): static
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    public function getEndDateTime(): ?\DateTimeInterface
    {
        return $this->endDateTime;
    }

    public function setEndDateTime(\DateTimeInterface $endDateTime): static
    {
        $this->endDateTime = $endDateTime;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

}
