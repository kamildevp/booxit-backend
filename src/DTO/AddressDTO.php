<?php

declare(strict_types=1);

namespace App\DTO;

use App\DTO\AbstractDTO;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

class AddressDTO extends AbstractDTO 
{
    public function __construct(
        #[Assert\NotBlank(allowNull: true)]
        #[Assert\Length(max: 255)]
        public readonly ?string $street,
        #[Assert\NotBlank(allowNull: true)]
        #[Assert\Length(max: 100)]
        public readonly ?string $city,
        #[Assert\NotBlank(allowNull: true)]
        #[Assert\Length(max: 100)]
        public readonly ?string $region,
        #[OA\Property(example: '30-126')]
        #[Assert\NotBlank(allowNull: true)]
        #[Assert\Regex(
            pattern: '/^[A-Z0-9 \-]{3,20}$/i',
            message: 'Invalid postal code format'
        )]
        public readonly ?string $postalCode,
        #[Assert\NotBlank(allowNull: true)]
        #[Assert\Length(max: 100)]
        public readonly ?string $country,
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public readonly string $placeId,
        #[Assert\NotBlank]
        public readonly string $formattedAddress,
        #[Assert\Range(min: -90, max: 90)]
        public readonly float $latitude,
        #[Assert\Range(min: -180, max: 180)]
        public readonly float $longitude,
    )
    {

    }
}