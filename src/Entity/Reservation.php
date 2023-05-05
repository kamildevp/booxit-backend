<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use App\Service\GetterHelper\Attribute\Getter;
use App\Service\SetterHelper\Attribute\Setter;
use App\Service\SetterHelper\Task\ReservationServiceTask;
use App\Service\SetterHelper\Task\ReservationTimeWindowTask;
use App\Service\SetterHelper\Task\ScheduleTask;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use DateTime;

#[Assert\GroupSequence(['basic','Reservation'])]
#[CustomAssert\Reservation]
#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Schedule $schedule = null;

    #[Assert\NotBlank(groups: ['Default','basic'])]
    #[Assert\Length(
        max: 180,
        maxMessage: 'Max length of email is 180 characters',
        groups: ['Default','basic']
    )]
    #[Assert\Email(
        message: 'Value is not a valid email.',
        groups: ['Default','basic']
    )]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[Assert\NotBlank(groups: ['Default','basic'])]
    #[Assert\Regex(
        pattern: '/^\d{6,20}$/',
        message: 'Value is not valid phone number',
        groups: ['Default','basic']
    )]
    #[ORM\Column(length: 255)]
    private ?string $phoneNumber = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Service $service = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?TimeWindow $timeWindow = null;

    #[ORM\Column]
    private ?bool $verified = null;

    #[ORM\Column]
    private ?bool $confirmed = null;

    #[Assert\NotBlank(groups: ['Default','basic'])]
    #[CustomAssert\DateTimeFormat(format: Schedule::DATE_FORMAT,groups: ['Default','basic'])]
    #[ORM\Column(length: 255)]
    private ?string $date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expiryDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Getter(groups: ['reservation-organization'])]
    public function getOrganization(): ?Organization
    {
        return $this->schedule->getOrganization();
    }

    #[Getter(groups: ['reservation-schedule'])]
    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    #[Setter(targetParameter: 'schedule_id', setterTask: ScheduleTask::class, groups: ['initOnly'])]
    public function setSchedule(?Schedule $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    #[Getter(groups: ['reservation'])]
    public function getDate(): ?string
    {
        return $this->date;
    }

    #[Setter]
    public function setDate(string $date): self
    {
        $this->date = $date;

        return $this;
    }

    #[Getter(groups: ['reservation'])]
    public function getEmail(): ?string
    {
        return $this->email;
    }

    #[Setter(groups:['initOnly'])]
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    #[Getter(groups: ['reservation'])]
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    #[Setter]
    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    #[Getter(groups: ['reservation-service'])]
    public function getService(): ?Service
    {
        return $this->service;
    }

    #[Setter(targetParameter: 'service_id', setterTask: ReservationServiceTask::class)]
    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    #[Getter(groups: ['reservation'])]
    public function getTimeWindow(): ?TimeWindow
    {
        return $this->timeWindow;
    }

    #[Setter(targetParameter: 'start_time', setterTask: ReservationTimeWindowTask::class)]
    public function setTimeWindow(TimeWindow $timeWindow): self
    {
        $this->timeWindow = $timeWindow;
        return $this;
    }

    #[Getter(groups: ['reservation'])]
    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): self
    {
        $this->verified = $verified;

        return $this;
    }

    #[Getter(groups: ['reservation'])]
    public function isConfirmed(): ?bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): self
    {
        $this->confirmed = $confirmed;

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

    public function updateTimeWindow(): void
    {
        if($this->timeWindow && $this->service){
            $startTime = $this->timeWindow->getStartTime();
            $duration = $this->service->getDuration();
    
            $endTime = (new DateTime)->setTimestamp($startTime->getTimestamp())->add($duration);

            $this->timeWindow->setEndTime($endTime);
        }
    }

}
