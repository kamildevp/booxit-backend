<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use App\Enum\NormalizerGroup;
use App\Enum\TimestampsColumns;
use App\Repository\Filter\EntityFilter\Attribute\Filter;
use App\Repository\Filter\EntityFilter\FieldValue;
use App\Repository\Order\EntityOrder\Attribute\Order;
use App\Repository\Order\EntityOrder\BaseFieldOrder;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable as DoctrineTimestampable;
use Symfony\Component\Serializer\Attribute\Groups;

trait Timestampable
{
    #[Groups([NormalizerGroup::TIMESTAMP->value])]
    #[Order(TimestampsColumns::CREATED_AT->value, new BaseFieldOrder)]   
    #[Filter('createdFrom', new FieldValue('>='))] 
    #[Filter('createdTo', new FieldValue('<='))] 
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[DoctrineTimestampable(on: 'create')]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups([NormalizerGroup::TIMESTAMP->value])]
    #[Order(TimestampsColumns::UPDATED_AT->value, new BaseFieldOrder)]   
    #[Filter('updatedFrom', new FieldValue('>='))] 
    #[Filter('updatedTo', new FieldValue('<='))] 
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[DoctrineTimestampable(on: 'update')]
    public ?\DateTimeImmutable $updatedAt = null;

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
    
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}