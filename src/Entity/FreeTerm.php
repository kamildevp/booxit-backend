<?php

namespace App\Entity;

use App\Repository\FreeTermRepository;
use App\Service\GetterHelper\Attribute\Getter;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FreeTermRepository::class)]
class FreeTerm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'freeTerms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Schedule $schedule = null;

    #[ORM\Column(length: 255)]
    private ?string $date = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?TimeWindow $timeWindow = null;

    public function getId(): ?int
    {
        return $this->id;
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

    #[Getter(groups: ['schedule-freeTerms'])]
    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): self
    {
        $this->date = $date;

        return $this;
    }

    #[Getter(groups: ['schedule-freeTerms'])]
    public function getTimeWindow(): ?TimeWindow
    {
        return $this->timeWindow;
    }

    public function setTimeWindow(TimeWindow $timeWindow): self
    {
        $this->timeWindow = $timeWindow;

        return $this;
    }
}
