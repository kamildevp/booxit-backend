<?php

namespace App\Service\DataHandlingHelper;

use App\Entity\TimeWindow;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use ReflectionMethod;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class DataHandlingHelper{

    public function mapMethodArguments(object $object, string $method, array $args):array
    {
        $mappedArgs = [];
        $parameters = $this->getMethodParameters($object, $method);

        foreach($parameters as $parameter){
            if(!key_exists($parameter->name, $args) && !$parameter->isDefaultValueAvailable()){
                throw new InvalidArgumentException("Parameter {$parameter->name} is required");
            }

            if(!key_exists($parameter->name, $args) && $parameter->isDefaultValueAvailable()){
                $mappedArgs[] = $parameter->getDefaultValue();
                continue;
            }

            $arg = $args[$parameter->name];
            if(!$parameter->hasType()){
                $mappedArgs[] = $arg;
                continue;
            }

            $parameterType = $parameter->getType();
            if($parameterType->isBuiltin()){
                settype($arg, $parameterType->getName());
                $mappedArgs[] = $arg;
            }    
        }
        return $mappedArgs;
    }

    public function getMethodParameters(object $object, string $method):array
    {
        if(!method_exists($object, $method)){
            $objectClass = get_class($object);
            throw new InvalidArgumentException("Method {$method} is not defined for object {$objectClass}");
        }
        $parameters = (new ReflectionMethod($object, $method))->getParameters();
        return $parameters;
    }

    public function replaceArrayKeys(array $array, array $keysReplacements):array
    {
        $orginalArray = $array;
        foreach($keysReplacements as $key => $newKey){
            if(!array_key_exists($key, $orginalArray)){
                continue;
            }

            unset($array[$key]);
            $array[$newKey] = $orginalArray[$key]; 
        }

        return $array;
    }

    public function findLooseStringMatch(string $string, array $stringArray, bool $matchSnakeCase = true, bool $matchCammelCase = true):?string
    {
        if(in_array($string, $stringArray)){
            return $string;
        }

        if($matchSnakeCase){
            $converter = new CamelCaseToSnakeCaseNameConverter();
            $snakeCase = $converter->normalize($string);
            if(in_array($snakeCase, $stringArray)){
                return $snakeCase;
            } 
        }

        if($matchCammelCase){
            $converter = new CamelCaseToSnakeCaseNameConverter();
            $cammelCase = $converter->denormalize($string);
            if(in_array($cammelCase, $stringArray)){
                return $cammelCase;
            } 
        }
        return null;
    }

    public function validateDateTime(string $dateTime, $format)
    {
        $dateTimeObject = DateTime::createFromFormat($format, $dateTime);
        return $dateTimeObject && $dateTimeObject->format($format) === $dateTime;
    }

    /** @param Collection<int, TimeWindow> $collection1 */
    /** @param Collection<int, TimeWindow> $collection2 */
    public function TimeWindowCollectionDiff(Collection $collection1, Collection $collection2):Collection
    {
        $diff = new ArrayCollection([]);
        if($collection2->count() === 0){
            return $this->mergeCollections($diff, $collection1);
        }

        $iterationCollection1 = new ArrayCollection($collection1->getValues());

        foreach($collection2 as $element2){
            if(!($element2 instanceof TimeWindow)){
                throw new InvalidArgumentException("Argument collection2 must be collection of TimeWindow objects");
            }

            $modifiedCollection1 = new ArrayCollection([]);

            foreach($iterationCollection1 as $element1){
                if(!($element1 instanceof TimeWindow)){
                    throw new InvalidArgumentException("Argument collection1 must be collection of TimeWindow objects");
                }

                $results = $this->substractTimeWindow($element1, $element2);

                if($results->count() === 1){
                    $modifiedCollection1->add($results->first());
                }
                else{
                    $modifiedCollection1 = $this->mergeCollections($modifiedCollection1, $results);
                }
            }
            $iterationCollection1 = new ArrayCollection($modifiedCollection1->getValues());
        }

        return $iterationCollection1;
    }

    public function substractTimeWindow(TimeWindow $timeWindow1, TimeWindow $timeWindow2):Collection
    {
        $results = new ArrayCollection();

        $startTime1 = $timeWindow1->getStartTime();
        $endTime1 = $timeWindow1->getEndTime();
        $startTime2 = $timeWindow2->getStartTime();
        $endTime2 = $timeWindow2->getEndTime();

        switch(true){
            case $endTime1 <= $startTime2 || $startTime1 >= $endTime2:
                $resultTimeWindow = (new TimeWindow)->setStartTime($startTime1)->setEndTime($endTime1);
                $results->add($resultTimeWindow);
                break;
            case $startTime1 >= $startTime2 && $endTime1 <= $endTime2:
                break;
            case $startTime1 == $startTime2:
                $resultTimeWindow = (new TimeWindow)->setStartTime($endTime2)->setEndTime($endTime1);
                $results->add($resultTimeWindow);
                break;
            case $endTime1 == $endTime2:
                $resultTimeWindow = (new TimeWindow)->setStartTime($startTime1)->setEndTime($startTime2);
                $results->add($resultTimeWindow);
                break;
            case $startTime1 > $startTime2:
                $resultTimeWindow= (new TimeWindow)->setStartTime($endTime2)->setEndTime($endTime1);
                $results->add($resultTimeWindow);
                break;
            default:
                $resultTimeWindow1 = (new TimeWindow)->setStartTime($startTime1)->setEndTime($startTime2);
                $resultTimeWindow2 = (new TimeWindow)->setStartTime($endTime2)->setEndTime($endTime1);
                $results->add($resultTimeWindow1);
                $results->add($resultTimeWindow2);
                break;   
        }
        return $results;

    }

    public function mergeCollections(Collection $collection1, Collection $collection2){
        $collection3 = new ArrayCollection(
            array_merge($collection1->toArray(), $collection2->toArray())
        );

        return $collection3;
    }

    public function getWeekDay(string $date, string $format){
        $weekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $dateTimeObject = DateTime::createFromFormat($format, $date);
        return $dateTimeObject ? $weekDays[$dateTimeObject->format('N')-1] : null;
    }

    public function getPrettyDateInterval(\DateInterval $dateInterval){
        $components['year'] = $dateInterval->y;
        $components['month'] = $dateInterval->m;
        $components['day'] = $dateInterval->d;
        $components['hour'] = $dateInterval->h;
        $components['minute'] = $dateInterval->i;
        $components['second'] = $dateInterval->y;

        $formattedInterval = '';
        foreach($components as $key => $component){
            if($component === 0){
                continue;
            }

            $name = abs($component) > 1 ? $key . 's' : $key;
            $formattedInterval = $formattedInterval . "{$component} {$name} ";
        }

        return $formattedInterval;
    }
}