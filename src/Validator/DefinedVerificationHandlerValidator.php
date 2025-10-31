<?php

declare(strict_types=1);

namespace App\Validator;

use App\Service\EmailConfirmation\EmailConfirmationHandlerInterface;
use App\Validator\Constraints\DefinedVerificationHandler;
use Exception;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class DefinedVerificationHandlerValidator extends ConstraintValidator
{
    public function __construct(protected EmailConfirmationHandlerInterface $emailConfirmationHandler)
    {
        
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof DefinedVerificationHandler) {
            throw new UnexpectedTypeException($constraint, DefinedVerificationHandler::class);
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        try{
            $this->emailConfirmationHandler->resolveVerificationHandlerUrl($value);
        }
        catch(Exception){
            $this->context->buildViolation($constraint->message)
            ->addViolation();
        }
    }
}