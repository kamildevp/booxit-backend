<?php

namespace App\Entity;

use App\Enum\OrganizationMember\OrganizationMemberNormalizerGroup;
use App\Repository\Filter\EntityFilter\FieldValue;
use App\Repository\Order\EntityOrder\BaseFieldOrder;
use App\Repository\OrganizationMemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OrganizationMemberRepository::class)]
#[ORM\UniqueConstraint(
    name: "unique_org_user",
    columns: ["organization_id", "app_user_id"]
)]
class OrganizationMember
{
    #[Groups([OrganizationMemberNormalizerGroup::BASE_INFO->value])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups([OrganizationMemberNormalizerGroup::ORGANIZATION->value])]
    #[ORM\ManyToOne(inversedBy: 'members')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Organization $organization = null;

    #[Groups([OrganizationMemberNormalizerGroup::USER->value])]
    #[ORM\ManyToOne(inversedBy: 'organizationAssignments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $appUser = null;

    #[ORM\OneToMany(mappedBy: 'organizationMember', targetEntity: ScheduleAssignment::class)]
    private Collection $scheduleAssignments;

    #[Groups([OrganizationMemberNormalizerGroup::BASE_INFO->value])]
    #[ORM\Column(length: 255)]
    private ?string $role = null;

    public function __construct()
    {
        $this->scheduleAssignments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getAppUser(): ?User
    {
        return $this->appUser;
    }

    public function setAppUser(?User $appUser): self
    {
        $this->appUser = $appUser;

        return $this;
    }

    /**
     * @return Collection<int, ScheduleAssignment>
     */
    public function getScheduleAssignments(): Collection
    {
        return $this->scheduleAssignments;
    }

    public function addScheduleAssignment(ScheduleAssignment $scheduleAssignment): self
    {
        if (!$this->scheduleAssignments->contains($scheduleAssignment)) {
            $this->scheduleAssignments->add($scheduleAssignment);
            $scheduleAssignment->setOrganizationMember($this);
        }

        return $this;
    }

    public function removeScheduleAssignment(ScheduleAssignment $scheduleAssignment): self
    {
        if ($this->scheduleAssignments->removeElement($scheduleAssignment)) {
            // set the owning side to null (unless already changed)
            if ($scheduleAssignment->getOrganizationMember() === $this) {
                $scheduleAssignment->setOrganizationMember(null);
            }
        }

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }


    public static function getFilterDefs(): array
    {
        return [
            'role' => new FieldValue('role', '='),
        ];
    }

    public static function getOrderDefs(): array
    {
        return [
            'id' => new BaseFieldOrder('id'),
            'role' => new BaseFieldOrder('role'),
        ];
    }
}
