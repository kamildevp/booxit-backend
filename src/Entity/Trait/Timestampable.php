<?php

namespace App\Entity\Trait;

use App\Enum\NormalizerGroup;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable as DoctrineTimestampable;
use Symfony\Component\Serializer\Attribute\Groups;

trait Timestampable
{
    #[Groups([NormalizerGroup::TIMESTAMP->value])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[DoctrineTimestampable(on: 'create')]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups([NormalizerGroup::TIMESTAMP->value])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[DoctrineTimestampable(on: 'update')]
    public ?\DateTimeImmutable $updatedAt = null;
}