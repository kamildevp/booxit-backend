<?php

declare(strict_types=1);

namespace App\Validator;

use App\DTO\WorkingHours\CustomWorkingHoursUpdateDTO;
use App\Model\TimeWindow;
use App\Repository\CustomTimeWindowRepository;
use App\Repository\ScheduleRepository;
use App\Service\Utils\DateTimeUtils;
use App\Validator\Constraints\CustomWorkingHours;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CustomWorkingHoursValidator extends ConstraintValidator
{
    public function __construct(
        private DateTimeUtils $dateTimeUtils,
        private ScheduleRepository $scheduleRepository,    
        private CustomTimeWindowRepository $customTimeWindowRepository,
        private RequestStack $requestStack,    
    )
    {
        
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CustomWorkingHours) {
            throw new UnexpectedTypeException($constraint, CustomWorkingHours::class);
        }

        if (!$value instanceof CustomWorkingHoursUpdateDTO) {
            throw new UnexpectedValueException($value, CustomWorkingHoursUpdateDTO::class);
        }

        if(empty($value->timeWindows)){
            return;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value->date);
        if($date === false){
            return;
        }

        $scheduleId = $this->requestStack->getCurrentRequest()?->attributes->get($constraint->scheduleRouteParameter);
        $schedule = $scheduleId ? $this->scheduleRepository->find($scheduleId) : null;
        if(!$schedule){
            return;
        }

        $customTimeWindowsToCheck = $this->customTimeWindowRepository->getScheduleCustomTimeWindows(
            $schedule, 
            $date->modify('-1 day'), 
            $date->modify('+1 day')
        );

        $customTimeWindowsToCheck = array_filter(
            $customTimeWindowsToCheck, 
            fn($customTimeWindow) => $customTimeWindow->getDate()->format('Y-m-d') != $value->date
        );

        $timeWindowsToCheck = array_map(fn($customTimeWindow) => TimeWindow::createFromDateAndTime(
            $customTimeWindow->getDate(),
            $customTimeWindow->getStartTime(),
            $customTimeWindow->getDate(),
            $customTimeWindow->getEndTime()
        ), $customTimeWindowsToCheck);

        $dateTimeWindows = array_map(fn($timeWindowDTO) => TimeWindow::createFromDateAndTime(
            $value->date,
            $timeWindowDTO->startTime,
            $value->date,
            $timeWindowDTO->endTime
        ), $value->timeWindows);

        $sortedTimeWindows = $this->dateTimeUtils->sortTimeWindowCollection(array_merge($timeWindowsToCheck, $dateTimeWindows));

        for($i = 0; $i < count($sortedTimeWindows) - 1; $i++){
            if($sortedTimeWindows[$i]->getEndTime() > $sortedTimeWindows[$i+1]->getStartTime()){
                $currentIterationDate = $sortedTimeWindows[$i]->getStartTime()->format('Y-m-d');
                $conflictingDate = $currentIterationDate == $value->date ? $sortedTimeWindows[$i+1]->getStartTime() : $sortedTimeWindows[$i]->getStartTime();
                $conflictingDateString = $conflictingDate->format('Y-m-d');
                $message = $conflictingDateString == $value->date ? $constraint->message : $constraint->anotherDateOverlapMessage;
                $path = $conflictingDateString == $value->date ? 'timeWindows.errors' : 'errors';

                $this->context->buildViolation($message)
                    ->setParameter('{{ date }}', $conflictingDateString)
                    ->atPath($path)
                    ->addViolation();
                return;
            }
        }
    }
}