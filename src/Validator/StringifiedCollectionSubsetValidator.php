<?php

declare(strict_types=1);

namespace App\Validator;

use App\Validator\Constraints\StringifiedCollectionSubset;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class StringifiedCollectionSubsetValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof StringifiedCollectionSubset) {
            throw new UnexpectedTypeException($constraint, StringifiedCollectionSubset::class);
        }

        if (null === $value || ($constraint->allowEmpty && '' === $value)) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $allowed = $constraint->baseCollection;
        if ($constraint->baseCollectionCallbackMethod !== null) {
            $object = $this->context->getObject();

            if (method_exists($object, $constraint->baseCollectionCallbackMethod)) {
                $allowed = $object->{$constraint->baseCollectionCallbackMethod}();
            }
        }

        $collection = explode($constraint->separator, $value);
        $validCollectionSubset = array_intersect($collection, $allowed);

        if (count($validCollectionSubset) != count($collection)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}