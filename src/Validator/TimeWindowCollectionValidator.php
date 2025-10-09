<?php

declare(strict_types=1);

namespace App\Validator;

use App\Validator\Constraints\TimeWindow;
use App\Validator\Constraints\TimeWindowCollection;
use DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TimeWindowCollectionValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof TimeWindowCollection) {
            throw new UnexpectedTypeException($constraint, TimeWindow::class);
        }

        if (!is_array($value)) {
            return;
        }

        $parsedTimeWindows = [];
        foreach($value as $timeWindow){
            $objectProperties = get_object_vars($timeWindow);
            if(!isset($objectProperties[$constraint->startTimeProperty]) || !isset($objectProperties[$constraint->endTimeProperty])){
                return;
            }

            $start = DateTime::createFromFormat('H:i', $objectProperties[$constraint->startTimeProperty]);
            $end = DateTime::createFromFormat('H:i', $objectProperties[$constraint->endTimeProperty]);

            if($start === false || $end == false){
                return;
            }
            $parsedTimeWindows[] = ['start' => $start, 'end' => $end];
        }

        uasort($parsedTimeWindows, function($timeWindow1, $timeWindow2){
            if($timeWindow1['start'] == $timeWindow2['start']){
                return 0;
            }
            return $timeWindow1['start'] > $timeWindow2['start'] ? 1 : -1;
        });

        for($i = 0; $i < count($parsedTimeWindows) - 1; $i++){
            if($parsedTimeWindows[$i]['end'] >= $parsedTimeWindows[$i+1]['start']){
                $this->context->buildViolation($constraint->message)->atPath('errors')->addViolation();
                return;
            }
        }
    }
}