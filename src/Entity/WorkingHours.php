<?php

namespace App\Entity;

use App\Repository\WorkingHoursRepository;
use App\Service\GetterHelper\Attribute\Getter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkingHoursRepository::class)]
class WorkingHours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $day = null;

    #[ORM\ManyToOne(inversedBy: 'workingHours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Schedule $schedule = null;

    #[ORM\JoinTable(name: 'working_hours_time_window')]
    #[ORM\JoinColumn(name: 'working_hours_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'time_window_id', referencedColumnName: 'id', unique: true)]
    #[ORM\ManyToMany(targetEntity: TimeWindow::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $timeWindows;

    public function __construct()
    {
        $this->timeWindows = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    #[Getter(groups:['schedule-workingHours'])]
    public function getDay(): ?string
    {
        return $this->day;
    }

    public function setDay(string $date): self
    {
        $this->day = $date;

        return $this;
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

    #[Getter(groups:['schedule-workingHours'])]
    /**
     * @return Collection<int, TimeWindow>
     */
    public function getTimeWindows(): Collection
    {
        return $this->timeWindows;
    }

    public function addTimeWindow(TimeWindow $timeWindow): self
    {
        if (!$this->timeWindows->contains($timeWindow)) {
            $this->timeWindows->add($timeWindow);
        }

        return $this;
    }

    public function removeTimeWindow(TimeWindow $timeWindow): self
    {
        $this->timeWindows->removeElement($timeWindow);
        
        return $this;
    }

}
