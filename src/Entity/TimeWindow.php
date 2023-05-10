<?php

namespace App\Entity;

use App\Repository\TimeWindowRepository;
use App\Service\GetterHelper\Attribute\Getter;
use App\Service\GetterHelper\CustomFormat\TimeFormat;
use App\Service\SetterHelper\Attribute\Setter;
use App\Service\SetterHelper\Task\TimeWindow\TimeWindowTask;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimeWindowRepository::class)]
class TimeWindow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $endTime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Getter(format: TimeFormat::class, groups: ['schedule-working_hours', 'schedule-freeTerms', 'reservation', 'schedule-reservations'])]
    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    #[Setter(setterTask: TimeWindowTask::class)]
    public function setStartTime(\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    #[Getter(format: TimeFormat::class, groups: ['schedule-working_hours', 'schedule-freeTerms', 'reservation', 'schedule-reservations'])]
    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }
}
