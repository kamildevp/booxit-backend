<?php

namespace App\Controller;

use App\Entity\Schedule;
use App\Exceptions\InvalidRequestException;
use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Service\GetterHelper\GetterHelperInterface;
use App\Service\SetterHelper\SetterHelperInterface;
use DateInterval;
use DateTime;
use Doctrine\Common\Cache\Psr6\InvalidArgument;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ScheduleController extends AbstractController
{
    #[Route('schedule', name: 'schedule_new', methods: ['POST'])]
    public function new(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper, 
        ValidatorInterface $validator, 
        Request $request
        ): JsonResponse
    {

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
                return $this->json([
                    'status' => 'Failure',
                    'message' => 'Validation Error',
                    'errors' => $validationErrors
                ]);
            }
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }

        $entityManager->persist($schedule);
        $entityManager->flush();

        return $this->json([
            'status' => 'Success',
            'message' => 'Schedule created successfully'
        ]);
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
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Requested details are invalid'
            ]);
        }

        $range = $request->query->get('range');
        $detailGroups = array_map(fn($group) => 'schedule-' . $group, $detailGroups);
        $groups = array_merge(['schedule'], $detailGroups);

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Schedule not found'
            ]);
        }
        
        try{
            $responseData = $getterHelper->get($schedule, $groups, $range);
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }

        return $this->json($responseData);
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

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Schedule not found'
            ]);
        }

        $organization = $schedule->getOrganization();
        $currentUser = $this->getUser();

        $member = $currentUser ? $organization->getMember($currentUser) : null;
        $assignment = $member ? $schedule->getAssignments()->findFirst(function($key, $element) use ($member){
            return $element->getOrganizationMember() == $member;
        }) : null;

        $hasWriteAccess = $member && ($member->hasRoles(['ADMIN']) || ($assignment ? $assignment->getAccessType() === 'WRITE' : false));

        if(!$hasWriteAccess){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Access Denied'
            ]);
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
                return $this->json([
                    'status' => 'Failure',
                    'message' => 'Validation Error',
                    'errors' => $validationErrors
                ]);
            }

            $setterHelper->runPostValidationTasks();
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }

        $entityManager->flush();

        return $this->json([
            'status' => 'Success',
            'message' => 'Schedule settings modified successfully'
        ]);
    }

    #[Route('schedule/{scheduleId}', name: 'schedule_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $scheduleId): JsonResponse
    {
        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Schedule not found'
            ]);
        }

        $organization = $schedule->getOrganization();
        $currentUser = $this->getUser();

        $member = $currentUser ? $organization->getMember($currentUser) : null;
        $assignment = $member ? $schedule->getAssignments()->findFirst(function($key, $element) use ($member){
            return $element->getOrganizationMember() == $member;
        }) : null;

        $hasWriteAccess = $member && ($member->hasRoles(['ADMIN']) || ($assignment ? $assignment->getAccessType() === 'WRITE' : false));

        if(!$hasWriteAccess){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Access Denied'
            ]);
        }

        $entityManager->remove($schedule);
        $entityManager->flush();

        return $this->json([
            'status' => 'Success',
            'message' => 'Schedule removed successfully'
        ]);
    }

    #[Route('schedule/{scheduleId}/services', name: 'schedule_modifyServices', methods: ['POST', 'PUT', 'DELETE'])]
    public function modifyServices(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper,
        Request $request, 
        int $scheduleId
        ): JsonResponse
    {
        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Schedule not found'
            ]);
        }

        $organization = $schedule->getOrganization();
        $currentUser = $this->getUser();

        $member = $currentUser ? $organization->getMember($currentUser) : null;
        $assignment = $member ? $schedule->getAssignments()->findFirst(function($key, $element) use ($member){
            return $element->getOrganizationMember() == $member;
        }) : null;

        $hasWriteAccess = $member && ($member->hasRoles(['ADMIN']) || ($assignment ? $assignment->getAccessType() === 'WRITE' : false));

        if(!$hasWriteAccess){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Access Denied'
            ]);
        }

        try{
            $modficationTypeMap = ['POST' => 'ADD', 'PUT' => 'OVERWRITE', 'DELETE' => 'REMOVE'];

            $parameters = $request->request->all();
            $parameters['modificationType'] = $modficationTypeMap[$request->getMethod()];
            $setterHelper->updateObjectSettings($schedule, $parameters, ['services'], []);

            $validationErrors = $setterHelper->getValidationErrors();

            if(count($validationErrors) > 0){
                return $this->json([
                    'status' => 'Failure',
                    'message' => 'Validation Error',
                    'errors' => $validationErrors
                ]);
            }
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }
    
        $entityManager->flush();

        $actionType = ['POST' => 'added', 'PUT' => 'overwritten', 'DELETE' => 'removed'];
        return $this->json([
            'status' => 'Success',
            'message' => "Services {$actionType[$request->getMethod()]} successfully"
        ]);
    }

    #[Route('schedule/{scheduleId}/assignments', name: 'schedule_modifyAssignments', methods: ['POST', 'PATCH', 'PUT', 'DELETE'])]
    public function modifyAssignments(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper,
        Request $request, 
        int $scheduleId
        ): JsonResponse
    {
        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Schedule not found'
            ]);
        }

        $organization = $schedule->getOrganization();
        $currentUser = $this->getUser();

        $member = $currentUser ? $organization->getMember($currentUser) : null;

        $hasWriteAccess = $member ? $member->hasRoles(['ADMIN']) : false;

        if(!$hasWriteAccess){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Access Denied'
            ]);
        }

        try{
            $modficationTypeMap = ['POST' => 'ADD', 'PATCH' => 'PATCH', 'PUT' => 'OVERWRITE', 'DELETE' => 'REMOVE'];

            $parameters = $request->request->all();
            $parameters['modificationType'] = $modficationTypeMap[$request->getMethod()];
            $setterHelper->updateObjectSettings($schedule, $parameters, ['assignments'], []);

            $validationErrors = $setterHelper->getValidationErrors();

            if(count($validationErrors) > 0){
                return $this->json([
                    'status' => 'Failure',
                    'message' => 'Validation Error',
                    'errors' => $validationErrors
                ]);
            }
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }
    
        $entityManager->flush();

        $actionType = ['POST' => 'added', 'PATCH' => 'modified', 'PUT' => 'overwritten', 'DELETE' => 'removed'];
        return $this->json([
            'status' => 'Success',
            'message' => "Assignments {$actionType[$request->getMethod()]} successfully"
        ]);
    }

    #[Route('schedule/{scheduleId}/working_hours', name: 'schedule_modifyWorkingHours', methods: ['POST', 'PATCH', 'PUT', 'DELETE'])]
    public function modifyWorkingHours(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper,
        Request $request, 
        int $scheduleId
        ): JsonResponse
    {
        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Schedule not found'
            ]);
        }

        $organization = $schedule->getOrganization();
        $currentUser = $this->getUser();

        $member = $currentUser ? $organization->getMember($currentUser) : null;
        $assignment = $member ? $schedule->getAssignments()->findFirst(function($key, $element) use ($member){
            return $element->getOrganizationMember() == $member;
        }) : null;

        $hasWriteAccess = $member && ($member->hasRoles(['ADMIN']) || ($assignment ? $assignment->getAccessType() === 'WRITE' : false));

        if(!$hasWriteAccess){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Access Denied'
            ]);
        }

        try{
            $modficationTypeMap = ['POST' => 'ADD', 'PATCH' => 'PATCH', 'PUT' => 'OVERWRITE', 'DELETE' => 'REMOVE'];

            $parameters = $request->request->all();
            $parameters['modificationType'] = $modficationTypeMap[$request->getMethod()];
            $setterHelper->updateObjectSettings($schedule, $parameters, ['workingHours'], []);

            $validationErrors = $setterHelper->getValidationErrors();

            if(count($validationErrors) > 0){
                return $this->json([
                    'status' => 'Failure',
                    'message' => 'Validation Error',
                    'errors' => $validationErrors
                ]);
            }
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }
    
        $entityManager->flush();

        $actionType = ['POST' => 'added', 'PATCH' => 'modified', 'PUT' => 'overwritten', 'DELETE' => 'removed'];
        return $this->json([
            'status' => 'Success',
            'message' => "Working hours {$actionType[$request->getMethod()]} successfully"
        ]);
    }

    #[Route('schedule/{scheduleId}/services', name: 'schedule_getServices', methods: ['GET'])]
    public function getServices(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, Request $request, int $scheduleId): JsonResponse
    {
        $filter = $request->query->get('filter');
        $range = $request->query->get('range');

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Schedule not found'
            ]);
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
        catch(InvalidRequestException $e){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }

        return $this->json($responseData);
    }

    #[Route('schedule/{scheduleId}/assignments', name: 'schedule_getAssignments', methods: ['GET'])]
    public function getAssigments(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, Request $request, int $scheduleId): JsonResponse
    {
        $filter = $request->query->get('filter');
        $range = $request->query->get('range');

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Schedule not found'
            ]);
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
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }

        return $this->json($responseData);
    }

    #[Route('schedule/{scheduleId}/working_hours', name: 'schedule_getWorkingHours', methods: ['GET'])]
    public function getWorkingHours(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, Request $request, int $scheduleId): JsonResponse
    {
        $filter = $request->query->get('filter');
        $range = $request->query->get('range');

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Schedule not found'
            ]);
        }
        
        if(is_null($filter)){
            $workingHours = $schedule->getWorkingHours();
        }
        else{
            $workingHours = $schedule->getWorkingHours()->filter(function($element) use ($filter){
                return str_contains(strtolower($element->getDay()), strtolower($filter));
            });
        }

        try{
            $responseData = $getterHelper->getCollection($workingHours, ['schedule-working_hours'], $range);
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }

        return $this->json($responseData);
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
            return $this->json([
                'message' => 'Range must be between 1 and 7'
            ]);
        }

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->json([
                'message' => 'Schedule not found'
            ]);
        }


        $dataHandlingHelper = new DataHandlingHelper();
        if(!$dataHandlingHelper->validateDateTime($date, Schedule::DATE_FORMAT)){
            $dateFormat = Schedule::DATE_FORMAT;
            return $this->json([
                'message' => "Date format must be {$dateFormat}"
            ]);
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

        return $this->json($freeTerms);
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

        $rangeRequest = $request->query->get('range'); 
        $range = !is_null($rangeRequest) ? (int)$rangeRequest : 1;
        if($range < 1 || $range > 7){
            return $this->json([
                'message' => 'Range must be between 1 and 7'
            ]);
        }

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->json([
                'message' => 'Schedule not found'
            ]);
        }

        $organization = $schedule->getOrganization();
        $currentUser = $this->getUser();

        $member = $currentUser ? $organization->getMember($currentUser) : null;
        $assignment = $member ? $schedule->getAssignments()->findFirst(function($key, $element) use ($member){
            return $element->getOrganizationMember() == $member;
        }) : null;

        $hasReadAccess = $member && ($member->hasRoles(['ADMIN']) || !is_null($assignment));
        if(!$hasReadAccess){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Access Denied'
            ]);
        }

        $dataHandlingHelper = new DataHandlingHelper();
        if(!$dataHandlingHelper->validateDateTime($date, Schedule::DATE_FORMAT)){
            $dateFormat = Schedule::DATE_FORMAT;
            return $this->json([
                'message' => "Date format must be {$dateFormat}"
            ]);
        }

        $dateTimeObject = DateTime::createFromFormat(Schedule::DATE_FORMAT, $date);
        for($i=0;$i<$range;$i++){
            $date = $dateTimeObject->format(Schedule::DATE_FORMAT);
            $dateReservations = $schedule->getDateReservations($date);
            $reservations[$date] = [];
            foreach($dateReservations as $reservation){
                $reservations[$date][] = $getterHelper->get($reservation, ['schedule-reservations']);
            }
            $dateTimeObject = $dateTimeObject->add(new DateInterval('P1D'));
        }

        return $this->json($reservations);
    }

}
