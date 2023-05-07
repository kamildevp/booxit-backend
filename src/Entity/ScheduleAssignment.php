<?php

namespace App\Entity;

use App\Repository\ScheduleAssignmentRepository;
use App\Service\GetterHelper\Attribute\Getter;
use App\Service\SetterHelper\Attribute\Setter;
use App\Service\SetterHelper\Task\ScheduleAssignmentAccessTypeTask;
use App\Service\SetterHelper\Task\ScheduleAssignmentMemberTask;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduleAssignmentRepository::class)]
class ScheduleAssignment
{

    const ACCESS_TYPES = ['READ', 'WRITE'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'scheduleAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Schedule $schedule = null;

    #[ORM\ManyToOne(inversedBy: 'scheduleAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?OrganizationMember $organizationMember = null;

    #[ORM\Column(length: 255)]
    private ?string $accessType = null;

    #[Getter(groups: ['schedule-assignments'])]
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

    #[Getter(propertyNameAlias: 'member', groups: ['schedule-assignments'])]
    public function getOrganizationMember(): ?OrganizationMember
    {
        return $this->organizationMember;
    }

    #[Setter(targetParameter: 'member_id', setterTask: ScheduleAssignmentMemberTask::class)]
    public function setOrganizationMember(?OrganizationMember $organizationMember): self
    {
        $this->organizationMember = $organizationMember;

        return $this;
    }

    #[Getter(groups: ['schedule-assignments'])]
    public function getAccessType(): ?string
    {
        return $this->accessType;
    }

    #[Setter(setterTask: ScheduleAssignmentAccessTypeTask::class)]
    public function setAccessType(string $accessType): self
    {
        $this->accessType = $accessType;

        return $this;
    }
}
