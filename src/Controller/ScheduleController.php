<?php

namespace App\Controller;

use App\Entity\Schedule;
use App\Exceptions\AccessDeniedException;
use App\Exceptions\InvalidRequestException;
use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Service\GetterHelper\GetterHelperInterface;
use App\Service\SetterHelper\SetterHelperInterface;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ScheduleController extends AbstractApiController
{
    #[Route('schedule', name: 'schedule_new', methods: ['POST'])]
    public function new(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper, 
        ValidatorInterface $validator, 
        Request $request
        ): JsonResponse
    {
        $currentUser = $this->getUser();
        if(!$currentUser){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 401);
        }

        $schedule = new Schedule();

        try{
            $setterHelper->updateObjectSettings($schedule, $request->request->all(), ['Default', 'initOnly']);
            $validationErrors = $setterHelper->getValidationErrors();
            
            $violations = $validator->validate($schedule, groups: $setterHelper->getValidationGroups());

            foreach ($violations as $violation) {
                $requestParameterName = $setterHelper->getPropertyRequestParameter($violation->getPropertyPath());
                $validationErrors[$requestParameterName] = $violation->getMessage();
            }

            if(count($validationErrors) > 0){
                return $this->newApiResponse(status: 'fail', data: ['message' => 'Validation Error', 'errors' => $validationErrors], code: 400);
            }

            $setterHelper->runPostValidationTasks();
        }
        catch(InvalidRequestException){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => $setterHelper->getRequestErrors()], code: 400);
        }
        catch(AccessDeniedException){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 403);
        }

        $entityManager->persist($schedule);
        $entityManager->flush();

        return $this->newApiResponse( data: ['message' => 'Schedule created successfully'], code: 201);
    }

    #[Route('schedule/{scheduleId}', name: 'schedule_get', methods: ['GET'])]
    public function get(
        EntityManagerInterface $entityManager, 
        GetterHelperInterface $getterHelper,  
        Request $request, 
        int $scheduleId
        ): JsonResponse
    {
        $allowedDetails = ['services', 'assignments', 'working_hours'];
        $details = $request->query->get('details');
        $detailGroups = !is_null($details) ? explode(',', $details) : [];
        if(!empty(array_diff($detailGroups, $allowedDetails))){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => ['details' => 'Requested details are invalid']], code: 400);
        }

        $range = $request->query->get('range');
        $detailGroups = array_map(fn($group) => 'schedule-' . $group, $detailGroups);
        $groups = array_merge(['schedule'], $detailGroups);

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Schedule not found'], code: 404);
        }
        
        try{
            $responseData = $getterHelper->get($schedule, $groups, $range);
        }
        catch(InvalidRequestException){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => $getterHelper->getRequestErrors()], code: 400);
        }

        return $this->newApiResponse(data: $responseData);
    }

    #[Route('schedule/{scheduleId}', name: 'schedule_modify', methods: ['PATCH'])]
    public function modify(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper, 
        ValidatorInterface $validator, 
        Request $request, 
        int $scheduleId
        ): JsonResponse
    {
        $currentUser = $this->getUser();
        if(!$currentUser){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 401);
        }

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Schedule not found'], code: 404);
        }

        $organization = $schedule->getOrganization();

        $member = $organization->getMember($currentUser);
        $assignment = $member ? $schedule->getAssignments()->findFirst(function($key, $element) use ($member){
            return $element->getOrganizationMember() == $member;
        }) : null;

        $hasWriteAccess = $member && ($member->hasRoles(['ADMIN']) || ($assignment ? $assignment->getAccessType() === 'WRITE' : false));

        if(!$hasWriteAccess){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 403);
        }


        try{
            $setterHelper->updateObjectSettings($schedule, $request->request->all(), [], ['Default']);
            $validationErrors = $setterHelper->getValidationErrors();
            
            $violations = $validator->validate($schedule, groups: $setterHelper->getValidationGroups());

            foreach ($violations as $violation) {
                $requestParameterName = $setterHelper->getPropertyRequestParameter($violation->getPropertyPath());
                $validationErrors[$requestParameterName] = $violation->getMessage();
            }            

            if(count($validationErrors) > 0){
                return $this->newApiResponse(status: 'fail', data: ['message' => 'Validation Error', 'errors' => $validationErrors], code: 400);
            }

            $setterHelper->runPostValidationTasks();
        }
        catch(InvalidRequestException){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => $setterHelper->getRequestErrors()], code: 400);
        }

        $entityManager->flush();

        return $this->newApiResponse( data: ['message' => 'Schedule settings modified successfully']);
    }

    #[Route('schedule/{scheduleId}', name: 'schedule_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $scheduleId): JsonResponse
    {
        $currentUser = $this->getUser();
        if(!$currentUser){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 401);
        }

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Schedule not found'], code: 404);
        }

        $organization = $schedule->getOrganization();

        $member = $organization->getMember($currentUser);
        $assignment = $member ? $schedule->getAssignments()->findFirst(function($key, $element) use ($member){
            return $element->getOrganizationMember() == $member;
        }) : null;

        $hasWriteAccess = $member && ($member->hasRoles(['ADMIN']) || ($assignment ? $assignment->getAccessType() === 'WRITE' : false));

        if(!$hasWriteAccess){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 403);
        }

        $entityManager->remove($schedule);
        $entityManager->flush();

        return $this->newApiResponse(data: ['message' => 'Schedule removed successfully']);
    }

    #[Route('schedule/{scheduleId}/services', name: 'schedule_modifyServices', methods: ['POST', 'PUT', 'DELETE'])]
    public function modifyServices(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper,
        Request $request, 
        int $scheduleId
        ): JsonResponse
    {
        $currentUser = $this->getUser();
        if(!$currentUser){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 401);
        }

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Schedule not found'], code: 404);
        }

        $organization = $schedule->getOrganization();
        $member = $organization->getMember($currentUser);
        $assignment = $member ? $schedule->getAssignments()->findFirst(function($key, $element) use ($member){
            return $element->getOrganizationMember() == $member;
        }) : null;

        $hasWriteAccess = $member && ($member->hasRoles(['ADMIN']) || ($assignment ? $assignment->getAccessType() === 'WRITE' : false));

        if(!$hasWriteAccess){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 403);
        }

        try{
            $modficationTypeMap = ['POST' => 'ADD', 'PUT' => 'OVERWRITE', 'DELETE' => 'REMOVE'];

            $parameters = $request->request->all();
            $parameters['modificationType'] = $modficationTypeMap[$request->getMethod()];
            $setterHelper->updateObjectSettings($schedule, $parameters, ['services'], []);

            $validationErrors = $setterHelper->getValidationErrors();

            if(count($validationErrors) > 0){
                return $this->newApiResponse(status: 'fail', data: ['message' => 'Validation Error', 'errors' => $validationErrors], code: 400);
            }
        }
        catch(InvalidRequestException $e){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => $setterHelper->getRequestErrors()], code: 400);
        }
    
        $entityManager->flush();

        $actionType = ['POST' => 'added', 'PUT' => 'overwritten', 'DELETE' => 'removed'];
        return $this->newApiResponse( data: ['message' => "Services {$actionType[$request->getMethod()]} successfully"]);
    }

    #[Route('schedule/{scheduleId}/assignments', name: 'schedule_modifyAssignments', methods: ['POST', 'PATCH', 'PUT', 'DELETE'])]
    public function modifyAssignments(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper,
        Request $request, 
        int $scheduleId
        ): JsonResponse
    {
        $currentUser = $this->getUser();
        if(!$currentUser){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 401);
        }

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Schedule not found'], code: 404);
        }

        $organization = $schedule->getOrganization();
        $member = $organization->getMember($currentUser);

        $hasWriteAccess = $member ? $member->hasRoles(['ADMIN']) : false;

        if(!$hasWriteAccess){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 403);
        }

        try{
            $modficationTypeMap = ['POST' => 'ADD', 'PATCH' => 'PATCH', 'PUT' => 'OVERWRITE', 'DELETE' => 'REMOVE'];

            $parameters = $request->request->all();
            $parameters['modificationType'] = $modficationTypeMap[$request->getMethod()];
            $setterHelper->updateObjectSettings($schedule, $parameters, ['assignments'], []);

            $validationErrors = $setterHelper->getValidationErrors();

            if(count($validationErrors) > 0){
                return $this->newApiResponse(status: 'fail', data: ['message' => 'Validation Error', 'errors' => $validationErrors], code: 400);
            }
        }
        catch(InvalidRequestException $e){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => $setterHelper->getRequestErrors()], code: 400);
        }
    
        $entityManager->flush();

        $actionType = ['POST' => 'added', 'PATCH' => 'modified', 'PUT' => 'overwritten', 'DELETE' => 'removed'];
        return $this->newApiResponse( data: ['message' => "Assignments {$actionType[$request->getMethod()]} successfully"]);
    }

    #[Route('schedule/{scheduleId}/working_hours', name: 'schedule_modifyWorkingHours', methods: ['POST', 'PATCH', 'PUT', 'DELETE'])]
    public function modifyWorkingHours(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper,
        Request $request, 
        int $scheduleId
        ): JsonResponse
    {
        $currentUser = $this->getUser();
        if(!$currentUser){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 401);
        }

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Schedule not found'], code: 404);
        }

        $organization = $schedule->getOrganization();
        $member = $organization->getMember($currentUser);
        $assignment = $member ? $schedule->getAssignments()->findFirst(function($key, $element) use ($member){
            return $element->getOrganizationMember() == $member;
        }) : null;

        $hasWriteAccess = $member && ($member->hasRoles(['ADMIN']) || ($assignment ? $assignment->getAccessType() === 'WRITE' : false));

        if(!$hasWriteAccess){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 403);
        }

        try{
            $modficationTypeMap = ['POST' => 'ADD', 'PATCH' => 'PATCH', 'PUT' => 'OVERWRITE', 'DELETE' => 'REMOVE'];

            $parameters = $request->request->all();
            $parameters['modificationType'] = $modficationTypeMap[$request->getMethod()];
            $setterHelper->updateObjectSettings($schedule, $parameters, ['workingHours'], []);

            $validationErrors = $setterHelper->getValidationErrors();

            if(count($validationErrors) > 0){
                return $this->newApiResponse(status: 'fail', data: ['message' => 'Validation Error', 'errors' => $validationErrors], code: 400);
            }
        }
        catch(InvalidRequestException $e){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => $setterHelper->getRequestErrors()], code: 400);
        }
    
        $entityManager->flush();

        $actionType = ['POST' => 'added', 'PATCH' => 'modified', 'PUT' => 'overwritten', 'DELETE' => 'removed'];
        return $this->newApiResponse( data: ['message' => "Working hours {$actionType[$request->getMethod()]} successfully"]);
    }

    #[Route('schedule/{scheduleId}/services', name: 'schedule_getServices', methods: ['GET'])]
    public function getServices(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, Request $request, int $scheduleId): JsonResponse
    {
        $filter = $request->query->get('filter');
        $range = $request->query->get('range');

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Schedule not found'], code: 404);
        }
        
        if(is_null($filter)){
            $services = $schedule->getServices();
        }
        else{
            $services = $schedule->getServices()->filter(function($element) use ($filter){
                return str_contains(strtolower($element->getName()), strtolower($filter));
            });
        }

        try{
            $responseData = $getterHelper->getCollection($services, ['schedule-services'], $range);
        }
        catch(InvalidRequestException){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => $getterHelper->getRequestErrors()], code: 400);
        }

        return $this->newApiResponse(data: $responseData);
    }

    #[Route('schedule/{scheduleId}/assignments', name: 'schedule_getAssignments', methods: ['GET'])]
    public function getAssigments(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, Request $request, int $scheduleId): JsonResponse
    {
        $filter = $request->query->get('filter');
        $range = $request->query->get('range');

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Schedule not found'], code: 404);
        }
        
        if(is_null($filter)){
            $assignments = $schedule->getAssignments();
        }
        else{
            $assignments = $schedule->getAssignments()->filter(function($element) use ($filter){
                $user = $element->getOrganizationMember()->getAppUser();
                return str_contains(strtolower($user->getName()), strtolower($filter)) || str_contains(strtolower($user->getEmail()), strtolower($filter));
            });
        }

        try{
            $responseData = $getterHelper->getCollection($assignments, ['schedule-assignments'], $range);
        }
        catch(InvalidRequestException $e){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => $getterHelper->getRequestErrors()], code: 400);
        }

        return $this->newApiResponse(data: $responseData);
    }

    #[Route('schedule/{scheduleId}/working_hours', name: 'schedule_getWorkingHours', methods: ['GET'])]
    public function getWorkingHours(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, Request $request, int $scheduleId): JsonResponse
    {
        $filter = $request->query->get('filter');
        $range = $request->query->get('range');

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Schedule not found'], code: 404);
        }
        
        if(is_null($filter)){
            $workingHours = $schedule->getWorkingHours();
        }
        else{
            $workingHours = $schedule->getWorkingHours()->filter(function($element) use ($filter){
                $matchFound = str_contains(strtolower($element->getDay()), strtolower($filter));
                if($matchFound){
                    return true;
                }

                $weekDay = (new DataHandlingHelper)->getWeekDay($filter, Schedule::DATE_FORMAT);
                if(is_null($weekDay)){
                    return false;
                }
                return str_contains(strtolower($element->getDay()), strtolower($weekDay));
            });
        }

        try{
            $responseData = $getterHelper->getCollection($workingHours, ['schedule-working_hours'], $range);
        }
        catch(InvalidRequestException $e){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => $getterHelper->getRequestErrors()], code: 400);
        }

        return $this->newApiResponse(data: $responseData);
    }

    #[Route('schedule/{scheduleId}/free_terms/{date}', name: 'schedule_getFreeTerms', methods: ['GET'])]
    public function getFreeTerms(
        EntityManagerInterface $entityManager, 
        GetterHelperInterface $getterHelper,  
        Request $request, 
        int $scheduleId,
        string $date, 
        ): JsonResponse
    {

        $rangeRequest = $request->query->get('range'); 
        $range = !is_null($rangeRequest) ? (int)$rangeRequest : 1;
        if($range < 1 || $range > 7){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => ['range' => 'Parameter must be between 1 and 7']], code: 400);
        }

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Schedule not found'], code: 404);
        }


        $dataHandlingHelper = new DataHandlingHelper();
        if(!$dataHandlingHelper->validateDateTime($date, Schedule::DATE_FORMAT)){
            $dateFormat = Schedule::DATE_FORMAT;
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => ['date' => "Date format must be {$dateFormat}"]], code: 400);
        }

        $dateTimeObject = DateTime::createFromFormat(Schedule::DATE_FORMAT, $date);
        for($i=0;$i<$range;$i++){
            $date = $dateTimeObject->format(Schedule::DATE_FORMAT);
            $dateFreeTerms = $schedule->getDateFreeTerms($date);
            $freeTerms[$date] = [];
            foreach($dateFreeTerms as $freeTerm){
                $freeTerms[$date][] = $getterHelper->get($freeTerm, ['schedule-freeTerms']);
            }
            $dateTimeObject = $dateTimeObject->add(new DateInterval('P1D'));
        }

        return $this->newApiResponse(data: $freeTerms);
    }

    #[Route('schedule/{scheduleId}/reservations/{date}', name: 'schedule_getReservations', methods: ['GET'])]
    public function getReservations(
        EntityManagerInterface $entityManager, 
        GetterHelperInterface $getterHelper,  
        Request $request, 
        int $scheduleId,
        string $date, 
        ): JsonResponse
    {
        $currentUser = $this->getUser();
        if(!$currentUser){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 401);
        }

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Schedule not found'], code: 404);
        }

        $requestErrors = [];

        $rangeRequest = $request->query->get('range'); 
        $range = !is_null($rangeRequest) ? (int)$rangeRequest : 1;
        if($range < 1 || $range > 7){
            $requestErrors['range'] = 'Parameter must be between 1 and 7';
        }

        $booleanStates = ['0', '1'];
        $verified = $request->query->get('verified'); 
        if(!is_null($verified) && !in_array($verified, $booleanStates)){
            $requestErrors['verfied'] = 'Parameter must be 0 or 1';
        }

        $confirmed = $request->query->get('confirmed'); 
        if(!is_null($confirmed) && !in_array($confirmed, $booleanStates)){
            $requestErrors['confirmed'] = 'Parameter must be 0 or 1';
        }

        $dataHandlingHelper = new DataHandlingHelper();
        if(!$dataHandlingHelper->validateDateTime($date, Schedule::DATE_FORMAT)){
            $dateFormat = Schedule::DATE_FORMAT;
            $requestErrors['date'] = "Date format must be {$dateFormat}";
        }

        if(!empty($requestErrors)){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => $requestErrors], code: 400);
        }

        $organization = $schedule->getOrganization();
        $member = $organization->getMember($currentUser);
        $assignment = $member ? $schedule->getAssignments()->findFirst(function($key, $element) use ($member){
            return $element->getOrganizationMember() == $member;
        }) : null;

        $hasReadAccess = $member && ($member->hasRoles(['ADMIN']) || !is_null($assignment));
        if(!$hasReadAccess){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 403);
        }

        $dateTimeObject = DateTime::createFromFormat(Schedule::DATE_FORMAT, $date);
        for($i = 0; $i < $range; $i++){
            $date = $dateTimeObject->format(Schedule::DATE_FORMAT);
            $dateReservations = $schedule->getDateReservations($date);
            $dateReservations = $dateReservations->filter(function($reservation) use ($verified, $confirmed){
                $verifiedMatch = !is_null($verified) ? $reservation->isVerified() == (bool)$verified : true;
                $confirmedMatch = !is_null($confirmed) ? $reservation->isConfirmed() == (bool) $confirmed : true;
                return  $verifiedMatch && $confirmedMatch;
            });

            $reservations[$date] = [];
            foreach($dateReservations as $reservation){
                $reservations[$date][] = $getterHelper->get($reservation, ['schedule-reservations']);
            }
            $dateTimeObject = $dateTimeObject->add(new DateInterval('P1D'));
        }

        return $this->newApiResponse(data: $reservations);
    }

}
