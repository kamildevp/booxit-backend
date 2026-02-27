<?php

declare(strict_types=1);

namespace App\Service\Auth\Social\DTO;

use App\DTO\AbstractDTO;
use App\Validator\Constraints\Compound as Compound;

class SocialOwnerDTO extends AbstractDTO 
{
    public function __construct(
        #[Compound\EmailRequirements]
        public readonly string $email,
        #[Compound\NameRequirements]
        public readonly string $name,
        public readonly ?string $locale,
        public readonly ?string $id
    )
    {

    }
}