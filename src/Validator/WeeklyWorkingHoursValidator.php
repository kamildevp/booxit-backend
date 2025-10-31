<?php

declare(strict_types=1);

namespace App\Validator;

use App\DTO\WorkingHours\WeeklyWorkingHoursUpdateDTO;
use App\Enum\Weekday;
use App\Model\TimeWindow;
use App\Service\Utils\DateTimeUtils;
use App\Validator\Constraints\WeeklyWorkingHours;
use DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class WeeklyWorkingHoursValidator extends ConstraintValidator
{
    public function __construct(private DateTimeUtils $dateTimeUtils)
    {
        
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof WeeklyWorkingHours) {
            throw new UnexpectedTypeException($constraint, WeeklyWorkingHours::class);
        }

        if (!$value instanceof WeeklyWorkingHoursUpdateDTO) {
            throw new UnexpectedValueException($value, WeeklyWorkingHoursUpdateDTO::class);
        }

        $parsedTimeWindows = [];
        $date = new DateTime();
        $weekdaysToParse = array_merge(Weekday::values(), [Weekday::MONDAY->value]);
        foreach($weekdaysToParse as $weekday){
            $dayTimeWindows = array_map(fn($timeWindowDTO) => TimeWindow::createFromDateAndTime(
                $date,
                $timeWindowDTO->startTime,
                $date,
                $timeWindowDTO->endTime
            ), $value->{$weekday});

            if(in_array(false, $parsedTimeWindows)){
                return;
            }

            $parsedTimeWindows = array_merge($parsedTimeWindows, $dayTimeWindows);
            $date->modify('+1 day');
        }

        $sortedTimeWindows = $this->dateTimeUtils->sortTimeWindowCollection($parsedTimeWindows);

        for($i = 0; $i < count($sortedTimeWindows) - 1; $i++){
            if($sortedTimeWindows[$i]->getEndTime() > $sortedTimeWindows[$i+1]->getStartTime()){
                $this->context->buildViolation($constraint->message)->atPath('errors')->addViolation();
                return;
            }
        }
    }
}