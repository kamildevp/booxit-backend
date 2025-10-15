<?php

declare(strict_types=1);

namespace App\Validator;

use App\DTO\WorkingHours\TimeWindowDTO;
use App\Model\TimeWindow;
use App\Service\Utils\DateTimeUtils;
use App\Validator\Constraints\TimeWindowLength;
use DateInterval;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class TimeWindowLengthValidator extends ConstraintValidator
{
    public function __construct(private DateTimeUtils $dateTimeUtils)
    {
        
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof TimeWindowLength) {
            throw new UnexpectedTypeException($constraint, TimeWindowLength::class);
        }

        if (!$value instanceof TimeWindowDTO) {
            throw new UnexpectedValueException($value, TimeWindowDTO::class);
        }

        $date = (new DateTimeImmutable())->format('Y-m-d');
        $timeWindow = TimeWindow::createFromDateAndTime($date, $value->startTime, $date, $value->endTime);
        if($timeWindow === false){
            return;
        }

        $minLength = $constraint->minLength ? DateInterval::createFromDateString($constraint->minLength) : null;
        $maxLength = $constraint->maxLength ? DateInterval::createFromDateString($constraint->maxLength) : null;

        if($minLength && $this->dateTimeUtils->compareDateIntervals($timeWindow->getLength(), $minLength) < 0){
            $this->context->buildViolation($constraint->minMessage)
                ->setParameter('{{ minLength }}', $constraint->minLength)
                ->atPath('errors')
                ->addViolation();
        }

        if($maxLength && $this->dateTimeUtils->compareDateIntervals($timeWindow->getLength(), $maxLength) > 0){
            $this->context->buildViolation($constraint->maxMessage)
                ->setParameter('{{ maxLength }}', $constraint->maxLength)
                ->atPath('errors')
                ->addViolation();
        }
    }
}