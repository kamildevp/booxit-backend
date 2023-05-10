<?php

namespace App\Entity;

use App\Repository\WorkingHoursRepository;
use App\Service\GetterHelper\Attribute\Getter;
use App\Service\SetterHelper\Attribute\Setter;
use App\Service\SetterHelper\Task\WorkingHours\DayTask;
use App\Service\SetterHelper\Task\WorkingHours\TimeWindowsTask;
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
    #[ORM\JoinColumn(name: 'working_hours_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'time_window_id', referencedColumnName: 'id', unique: true, onDelete: 'CASCADE')]
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

    #[Getter(groups:['schedule-working_hours'])]
    public function getDay(): ?string
    {
        return $this->day;
    }

    #[Setter(setterTask: DayTask::class)]
    public function setDay(string $day): self
    {
        $this->day = $day;

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

    #[Getter(groups:['schedule-working_hours'])]
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

    #[Setter(setterTask: TimeWindowsTask::class)]
    public function setTimeWindows(Collection $timeWindows): self
    {
        $this->timeWindows = $timeWindows;

        return $this;
    }

}
