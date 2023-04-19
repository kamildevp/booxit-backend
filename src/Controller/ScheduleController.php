<?php

namespace App\Controller;

use App\Entity\Schedule;
use App\Exceptions\InvalidRequestException;
use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Service\GetterHelper\GetterHelperInterface;
use App\Service\SetterHelper\SetterHelperInterface;
use DateInterval;
use DateTime;
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
            $setterHelper->updateObjectSettings($schedule, $request->request->all(), ['Default', 'initOnly'], ['services', 'workingHours', 'assignments']);
            $violations = $validator->validate($schedule, groups: $setterHelper->getValidationGroups());
            
            if(count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $requestParameterName = $setterHelper->getPropertyRequestParameter($violation->getPropertyPath());
                    $errors[$requestParameterName][] = $violation->getMessage();
                }
                return $this->json([
                    'message' => 'Validation Error',
                    'errors' => $errors
                ]);
            }
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }

        $setterHelper->runPostValidationTasks();

        $entityManager->persist($schedule);
        $entityManager->flush();

        return $this->json([
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
        $allowedDetails = ['services', 'assignments', 'workingHours'];
        $details = $request->query->get('details');
        $detailGroups = !is_null($details) ? explode(',', $details) : [];
        if(!empty(array_diff($detailGroups, $allowedDetails))){
            return $this->json([
                'message' => 'Requested details are invalid'
            ]);
        }

        $detailGroups = array_map(fn($group) => 'schedule-' . $group, $detailGroups);
        $groups = array_merge(['schedule'], $detailGroups);

        $schedule = $entityManager->getRepository(Schedule::class)->find($scheduleId);
        if(!($schedule instanceof Schedule)){
            return $this->json([
                'message' => 'Schedule not found'
            ]);
        }

        $responseData = $getterHelper->get($schedule, $groups);

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
                'message' => 'Schedule not found'
            ]);
        }

        $organization = $schedule->getOrganization();
        $currentUser = $this->getUser();

        if(!($currentUser && $organization->hasMember($currentUser) && $organization->getMember($currentUser)->hasRoles(['ADMIN']))){
            return $this->json([
                'message' => 'Access Denied'
            ]);
        }


        try{
            $setterHelper->updateObjectSettings($schedule, $request->request->all(), [], ['Default', 'services', 'workingHours', 'assignments']);
            $violations = $validator->validate($schedule, groups: $setterHelper->getValidationGroups());
            
            if(count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $requestParameterName = $setterHelper->getPropertyRequestParameter($violation->getPropertyPath());
                    $errors[$requestParameterName][] = $violation->getMessage();
                }
                return $this->json([
                    'message' => 'Validation Error',
                    'errors' => $errors
                ]);
            }
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }

        $setterHelper->runPostValidationTasks();

        $entityManager->flush();

        return $this->json([
            'message' => 'Schedule modified successfully'
        ]);
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
        $range = !is_null($rangeRequest) ? (int)$rangeRequest : 7;
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

        $dateFormat = 'Y-m-d';
        $dataHandlingHelper = new DataHandlingHelper();
        if(!$dataHandlingHelper->validateDateTime($date, $dateFormat)){
            return $this->json([
                'message' => 'Date format must be Y-m-d'
            ]);
        }

        $dateTimeObject = DateTime::createFromFormat($dateFormat, $date);
        $dates[] = $date;
        for($i=1;$i<$range;$i++){
            $dateTimeObject = $dateTimeObject->add(new DateInterval('P1D'));
            $dates[] = $dateTimeObject->format($dateFormat);
        }

        $freeTermsCollection = $schedule->getFreeTerms();
        $filtredFreeTerms = $freeTermsCollection->filter(function($key, $element) use ($dates){
            return in_array($element->getDate(), $dates);
        });

        $responseData = $getterHelper->get($filtredFreeTerms, ['schedule-freeTerms']);

        return $this->json($responseData);
    }


}
