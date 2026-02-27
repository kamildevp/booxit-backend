<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Enum\Auth\Social\SocialAuthProvider;
use App\Validator\DefinedSocialAuthHandlerValidator;
use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class DefinedSocialAuthHandler extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public SocialAuthProvider $providerType,
        public string $message = 'Invalid auth handler',
        ?array $groups = null,
        mixed $payload = null,
    ) 
    {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy()
    {
        return DefinedSocialAuthHandlerValidator::class;
    }
}