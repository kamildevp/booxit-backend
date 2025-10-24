<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ScheduleAssignment\ScheduleAssignmentNormalizerGroup;
use App\Repository\Filter\EntityFilter\FieldValue;
use App\Repository\Order\EntityOrder\BaseFieldOrder;
use App\Repository\ScheduleAssignmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ScheduleAssignmentRepository::class)]
class ScheduleAssignment
{
    #[Groups([ScheduleAssignmentNormalizerGroup::BASE_INFO->value])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups([ScheduleAssignmentNormalizerGroup::SCHEDULE->value])]
    #[ORM\ManyToOne(inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Schedule $schedule = null;

    #[Groups([ScheduleAssignmentNormalizerGroup::ORGANIZATION_MEMBER->value])]
    #[ORM\ManyToOne(inversedBy: 'scheduleAssignments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?OrganizationMember $organizationMember = null;

    #[Groups([ScheduleAssignmentNormalizerGroup::BASE_INFO->value])]
    #[ORM\Column(length: 255)]
    private ?string $accessType = null;

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

    public function getOrganizationMember(): ?OrganizationMember
    {
        return $this->organizationMember;
    }

    public function setOrganizationMember(?OrganizationMember $organizationMember): self
    {
        $this->organizationMember = $organizationMember;

        return $this;
    }

    public function getAccessType(): ?string
    {
        return $this->accessType;
    }

    public function setAccessType(string $accessType): self
    {
        $this->accessType = $accessType;

        return $this;
    }

    public static function getFilterDefs(): array
    {
        return [
            'accessType' => new FieldValue('accessType', '='),
        ];
    }

    public static function getOrderDefs(): array
    {
        return [
            'id' => new BaseFieldOrder('id'),
            'access_type' => new BaseFieldOrder('accessType'),
        ];
    }
}
