<?php

declare(strict_types=1);

namespace App\Validator;

use App\Service\Auth\Social\SocialAuthProviderInterface;
use App\Validator\Constraints\DefinedSocialAuthHandler;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class DefinedSocialAuthHandlerValidator extends ConstraintValidator
{
    /**
     * @param iterable<SocialAuthProviderInterface> $socialAuthProviders
     */
    public function __construct(#[TaggedIterator('social_auth_provider')] protected iterable $socialAuthProviders)
    {
        
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof DefinedSocialAuthHandler) {
            throw new UnexpectedTypeException($constraint, DefinedSocialAuthHandler::class);
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $valid = false;
        try{
            foreach($this->socialAuthProviders as $provider){
                if($provider->getProviderType() == $constraint->providerType){
                    $provider->resolveAuthHandlerRedirectUrl($value, $constraint->providerType);
                    $valid = true;
                }
            }
        }
        catch(Exception){
            $valid = false;
        }

        if(!$valid){
            $this->context->buildViolation($constraint->message)
            ->addViolation();
        }
    }
}