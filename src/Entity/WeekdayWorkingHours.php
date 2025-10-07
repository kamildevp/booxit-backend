<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\WeekdayWorkingHours\WeekdayWorkingHoursNormalizerGroup;
use App\Repository\WeekdayWorkingHoursRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: WeekdayWorkingHoursRepository::class)]
class WeekdayWorkingHours
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID, unique: true)]
    private ?string $id = null;

    #[ORM\ManyToOne(inversedBy: 'weekdayWorkingHours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Schedule $schedule = null;

    #[Groups([WeekdayWorkingHoursNormalizerGroup::DEFAULT->value])]
    #[ORM\Column(length: 255)]
    private ?string $weekday = null;

    #[Groups([WeekdayWorkingHoursNormalizerGroup::DEFAULT->value])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'H:i'])]
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $startTime = null;

    #[Groups([WeekdayWorkingHoursNormalizerGroup::DEFAULT->value])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'H:i'])]
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $endTime = null;

    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(?Schedule $schedule): static
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getWeekday(): ?string
    {
        return $this->weekday;
    }

    public function setWeekday(string $weekday): static
    {
        $this->weekday = $weekday;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }
}
