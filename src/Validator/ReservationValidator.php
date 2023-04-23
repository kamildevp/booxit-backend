<?php

namespace App\Validator;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use App\Entity\Reservation;
use App\Entity\Schedule;
use App\Validator\Constraints\Reservation as ReservationConstraint;
use DateTime;

class ReservationValidator extends ConstraintValidator
{
        /**
     * @param Reservation $receipt
     */
    public function validate($reservation, Constraint $constraint): void
    {
        if (!$reservation instanceof Reservation) {
            throw new UnexpectedValueException($reservation, Reservation::class);
        }

        if (!$constraint instanceof ReservationConstraint) {
            throw new UnexpectedValueException($constraint, ReservationConstraint::class);
        }

        $date = $reservation->getDate();
        if(is_null($date)){
            return;
        }

        $timeWindow = $reservation->getTimeWindow();
        if(!$timeWindow){
            return;
        }

        $schedule = $reservation->getSchedule();
        
        $endTime = $timeWindow->getEndTime();
        $endDateTime = DateTime::createFromFormat(Schedule::DATE_FORMAT, $date)->setTime((int)$endTime->format('H'), (int)$endTime->format('i'));

        if($endDateTime <= new DateTime('now')){
            $matchingTermExist = false;
        }
        else{
            $schedule->removeReservation($reservation);
            $freeTerms = $schedule->getDateFreeTerms($date);
    
            $matchingTermExist = $freeTerms->exists(function($key, $element) use ($timeWindow){
                return $element->getStartTime() <= $timeWindow->getStartTime() && $element->getEndTime() >= $timeWindow->getEndTime();
            });
    
            $schedule->addReservation($reservation);
        }

       
        if (!$matchingTermExist) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('reservation')
                ->addViolation();
        }
    }
}