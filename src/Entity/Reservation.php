<?php

namespace App\Entity;

use App\Entity\Trait\Blameable;
use App\Entity\Trait\Timestampable;
use App\Enum\Reservation\ReservationNormalizerGroup;
use App\Repository\Filter\EntityFilter\DateTimeFieldValue;
use App\Repository\Filter\EntityFilter\FieldContains;
use App\Repository\Filter\EntityFilter\FieldInSet;
use App\Repository\Filter\EntityFilter\FieldValue;
use App\Repository\Order\EntityOrder\BaseFieldOrder;
use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\SoftDeleteable as DoctrineSoftDeleteable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[DoctrineSoftDeleteable]
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
    #[ORM\Column(length: 255, unique: true)]
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
    #[ORM\JoinColumn(nullable: false)]
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
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $startDateTime = null;

    #[Groups([ReservationNormalizerGroup::DETAILS->value])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $endDateTime = null;

    #[Groups([ReservationNormalizerGroup::ORGANIZATION_ONLY->value])]
    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[Groups([ReservationNormalizerGroup::DETAILS->value])]
    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[Groups([ReservationNormalizerGroup::ORGANIZATION->value])]
    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

    #[Groups([ReservationNormalizerGroup::USER->value])]
    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?User $reservedBy = null;

    #[ORM\JoinTable(name: 'reservation_email_confirmation')]
    #[ORM\JoinColumn(name: 'reservation_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'email_confirmation_id', referencedColumnName: 'id', unique: true, onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: EmailConfirmation::class, cascade: ['remove'])]
    private Collection $emailConfirmations;

    public function __construct()
    {
        $this->emailConfirmations = new ArrayCollection();
    }

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

    public function getStartDateTime(): ?\DateTimeImmutable
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(\DateTimeImmutable $startDateTime): static
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    public function getEndDateTime(): ?\DateTimeImmutable
    {
        return $this->endDateTime;
    }

    public function setEndDateTime(\DateTimeImmutable $endDateTime): static
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

    public function setOrganization(?Organization $organization): static
    {
        $this->organization = $organization;

        return $this;
    }

    public function getReservedBy(): ?User
    {
        return $this->reservedBy;
    }

    public function setReservedBy(?User $reservedBy): static
    {
        $this->reservedBy = $reservedBy;

        return $this;
    }

    /**
     * @return Collection<int, EmailConfirmation>
     */
    public function getEmailConfirmations(): Collection
    {
        return $this->emailConfirmations;
    }

    public function addEmailConfirmation(EmailConfirmation $emailConfirmation): static
    {
        if (!$this->emailConfirmations->contains($emailConfirmation)) {
            $this->emailConfirmations->add($emailConfirmation);
        }

        return $this;
    }

    public function removeEmailConfirmation(EmailConfirmation $emailConfirmation): static
    {
        $this->emailConfirmations->removeElement($emailConfirmation);

        return $this;
    }

    public static function getFilterDefs(): array
    {
        return array_merge(self::getTimestampsFilterDefs(), [
            'organizationId' => new FieldInSet('organization'),
            'scheduleId' => new FieldInSet('schedule'),
            'serviceId' => new FieldInSet('service'),
            'reference' => new FieldContains('reference'),
            'email' => new FieldContains('email'),
            'phoneNumber' => new FieldContains('phoneNumber'),
            'verified' => new FieldValue('verified', '='),
            'expiryDateFrom' => new DateTimeFieldValue('expiryDate', '>='),
            'expiryDateTo' => new DateTimeFieldValue('expiryDate', '<='),
            'estimatedPriceFrom' => new FieldValue('estimatedPrice', '>='),
            'estimatedPriceTo' => new FieldValue('estimatedPrice', '<='),
            'startDateTimeFrom' => new DateTimeFieldValue('startDateTime', '>='),
            'startDateTimeTo' => new DateTimeFieldValue('startDateTime', '<='),
            'endDateTimeFrom' => new DateTimeFieldValue('endDateTime', '>='),
            'endDateTimeTo' => new DateTimeFieldValue('endDateTime', '<='),
            'type' => new FieldInSet('type'),
            'status' => new FieldInSet('status'),
            'reservedById' => new FieldInSet('reservedBy'),
        ]);
    }

    public static function getOrderDefs(): array
    {
        return array_merge(self::getTimestampsOrderDefs(), [
            'id' => new BaseFieldOrder('id'),
            'reference' => new BaseFieldOrder('reference'),
            'email' => new BaseFieldOrder('email'),
            'verified' => new BaseFieldOrder('verified'),
            'expiry_date' => new BaseFieldOrder('expiryDate'),
            'estimated_price' => new BaseFieldOrder('estimatedPrice'),
            'start_date_time' => new BaseFieldOrder('startDateTime'),
            'end_date_time' => new BaseFieldOrder('endDateTime'),
            'type' => new BaseFieldOrder('type'),
            'status' => new BaseFieldOrder('status'),
        ]);
    }

}
