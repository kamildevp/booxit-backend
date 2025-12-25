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
use DateTimeZone;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CustomWorkingHoursValidator extends ConstraintValidator
{
    private DateTimeZone $defaultTimezone;

    public function __construct(
        private DateTimeUtils $dateTimeUtils,
        private ScheduleRepository $scheduleRepository,    
        private CustomTimeWindowRepository $customTimeWindowRepository,
        private RequestStack $requestStack,    
        #[Autowire('%timezone%')]private string $defaultTimezoneString,
    )
    {
        $this->defaultTimezone = new DateTimeZone($defaultTimezoneString);
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

        try{
            $timezone = new DateTimeZone($value->timezone);
        }
        catch(Exception){
            return;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value->date, $timezone);
        if($date === false){
            return;
        }

        $scheduleId = $this->requestStack->getCurrentRequest()?->attributes->get($constraint->scheduleRouteParameter);
        $schedule = $scheduleId ? $this->scheduleRepository->find($scheduleId) : null;
        if(!$schedule){
            return;
        }

        $startDateTime = $date->setTime(0,0)->setTimezone($this->defaultTimezone);
        $endDateTime = $date->setTime(23,59)->setTimezone($this->defaultTimezone);
        $customTimeWindowsToCheck = $this->customTimeWindowRepository->getScheduleCustomTimeWindows(
            $schedule, 
            $startDateTime->modify('-1 day'), 
            $endDateTime->modify('+1 day')
        );

        $customTimeWindowsToCheck = array_filter(
            $customTimeWindowsToCheck, 
            fn($customTimeWindow) => 
                $customTimeWindow->getStartDateTime() < $startDateTime ||
                $customTimeWindow->getStartDateTime() > $endDateTime
        );

        $timeWindowsToCheck = array_map(fn($customTimeWindow) => 
            new TimeWindow(
                $customTimeWindow->getStartDateTime()->setTimezone($timezone),
                $customTimeWindow->getEndDateTime()->setTimezone($timezone)
            )
        , $customTimeWindowsToCheck);

        $dateTimeWindows = array_map(fn($timeWindowDTO) => TimeWindow::createFromDateAndTime(
            $value->date,
            $timeWindowDTO->startTime,
            $value->date,
            $timeWindowDTO->endTime,
            $timezone
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