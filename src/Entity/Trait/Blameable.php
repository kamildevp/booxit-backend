<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use App\Entity\User;
use App\Enum\NormalizerGroup;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;

trait Blameable
{
    #[Groups([self::class.NormalizerGroup::AUTHOR_INFO->value])]
    #[Gedmo\Blameable(on: 'create')]
    #[ORM\ManyToOne]
    private ?User $createdBy = null;

    #[Groups([self::class.NormalizerGroup::AUTHOR_INFO->value])]
    #[Gedmo\Blameable(on: 'update')]
    #[ORM\ManyToOne]
    private ?User $updatedBy = null;

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): static
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }
}