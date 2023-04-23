<?php

namespace App\Controller;

use App\Entity\EmailConfirmation;
use App\Entity\Reservation;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\MailingHelperException;
use App\Service\GetterHelper\GetterHelperInterface;
use App\Service\MailingHelper\MailingHelper;
use App\Service\SetterHelper\SetterHelperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class ReservationController extends AbstractController
{
    #[Route('reservation', name: 'reservation_new', methods: ['POST'])]
    public function new(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper, 
        ValidatorInterface $validator, 
        MailingHelper $mailingHelper, 
        Request $request
        ): JsonResponse
    {

        $reservation = new Reservation();

        try{
            $setterHelper->updateObjectSettings($reservation, $request->request->all(), ['Default', 'initOnly']);
            $validationErrors = $setterHelper->getValidationErrors();
            
            $violations = $validator->validate($reservation, groups: $setterHelper->getValidationGroups());

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

        $reservation->setVerified(false);
        $reservation->setConfirmed(false);

        $entityManager->persist($reservation);
        $entityManager->flush();
        
        try{
            $mailingHelper->newReservationVerification($reservation);
        }
        catch(MailingHelperException){
            $entityManager->remove($reservation);
            $entityManager->flush();
            return $this->json([
                'status' => 'Failure',
                'message' => 'Server Error'
            ]);
        }

        return $this->json([
            'status' => 'Success',
            'message' => 'Reservation created successfully'
        ]);
    }

    #[Route('reservation_verify', name: 'reservation_verify', methods: ['GET'])]
    public function verify(
        EntityManagerInterface $entityManager, 
        VerifyEmailHelperInterface $verifyEmailHelper, 
        MailingHelper $mailingHelper,
        Request $request
        )
    {
        $id = (int)$request->get('id');
        $emailConfirmation = $entityManager->getRepository(EmailConfirmation::class)->find($id);
        if(!($emailConfirmation instanceof EmailConfirmation)){
            return $this->render(
                'reservationVerification.html.twig', 
                ['header' => 'Verification Failed', 'description' => 'Verification link is invalid']
            );
        }

        try{
            $verifyEmailHelper->validateEmailConfirmation($request->getUri(), $emailConfirmation->getId(), $emailConfirmation->getEmail());
            $reservationId = $emailConfirmation->getParams()['reservationId'];
            $reservation = $entityManager->getRepository(Reservation::class)->find($reservationId);
            if(!($reservation instanceof Reservation)){
                return $this->render(
                    'reservationVerification.html.twig', 
                    ['header' => 'Verification Failed', 'description' => 'Reservation not found']
                );
            }
            $mailingHelper->newReservationCancellation($reservation);

            $reservation->setVerified(true);
            $reservation->setExpiryDate(null);
            $entityManager->remove($emailConfirmation);
            $entityManager->flush();
            return $this->render(
                'reservationVerification.html.twig', 
                ['header' => 'Verification Completed', 'description' => 'Your reservation was verfied successfully']
            );

        } 
        catch(VerifyEmailExceptionInterface $e) {
            return $this->render(
                'reservationVerification.html.twig', 
                ['header' => 'Verification Failed', 'description' => $e->getReason()]
            );
        }
        catch(MailingHelperException){
            return $this->render(
                'reservationVerification.html.twig', 
                ['header' => 'Verification Failed', 'description' => 'Server Error']
            );
        }

        

    }

    #[Route('reservation_cancel', name: 'reservation_cancel', methods: ['GET'])]
    public function cancel(
        EntityManagerInterface $entityManager, 
        VerifyEmailHelperInterface $verifyEmailHelper,  
        Request $request
        )
    {
        $id = (int)$request->get('id');
        $emailConfirmation = $entityManager->getRepository(EmailConfirmation::class)->find($id);
        if(!($emailConfirmation instanceof EmailConfirmation)){
            return $this->render(
                'reservationCancellation.html.twig', 
                ['header' => 'Cancellation Failed', 'description' => 'Cancellation link is invalid']
            );
        }

        try{
            $verifyEmailHelper->validateEmailConfirmation($request->getUri(), $emailConfirmation->getId(), $emailConfirmation->getEmail());
            $reservationId = $emailConfirmation->getParams()['reservationId'];
            $reservation = $entityManager->getRepository(Reservation::class)->find($reservationId);
            if(!($reservation instanceof Reservation)){
                return $this->render(
                    'reservationCancellation.html.twig', 
                    ['header' => 'Cancelation Failed', 'description' => 'Reservation not found']
                );
            }

            $entityManager->remove($reservation);
            $entityManager->remove($emailConfirmation);
            $entityManager->flush();

            return $this->render(
                'reservationCancellation.html.twig', 
                ['header' => 'Reservation Cancelled', 'description' => 'Your reservation was cancelled']
            );

        } 
        catch(VerifyEmailExceptionInterface $e) {
            return $this->render(
                'reservationCancellation.html.twig', 
                ['header' => 'Cancellation Failed', 'description' => $e->getReason()]
            );
        }
    }

    #[Route('reservation/{reservationId}', name: 'reservation_get', methods: ['GET'])]
    public function get(
        EntityManagerInterface $entityManager, 
        GetterHelperInterface $getterHelper,  
        Request $request, 
        int $reservationId
        ): JsonResponse
    {
        $allowedDetails = ['organization', 'schedule', 'service'];
        $details = $request->query->get('details');
        $detailGroups = !is_null($details) ? explode(',', $details) : [];
        if(!empty(array_diff($detailGroups, $allowedDetails))){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Requested details are invalid'
            ]);
        }

        $detailGroups = array_map(fn($group) => 'reservation-' . $group, $detailGroups);
        $groups = array_merge(['reservation'], $detailGroups);

        $reservation = $entityManager->getRepository(Reservation::class)->find($reservationId);
        if(!($reservation instanceof Reservation)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Reservation not found'
            ]);
        }

        $schedule = $reservation->getSchedule();
        $organization = $schedule->getOrganization();
        $currentUser = $this->getUser();
        if(!($currentUser && $organization->hasMember($currentUser))){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Access Denied'
            ]);
        }

        $organizationMember = $organization->getMember($currentUser);
        $scheduleAssignment = $organizationMember->getScheduleAssignment($schedule);

        if(!$organizationMember->hasRoles(['ADMIN']) && !$scheduleAssignment){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Access Denied'
            ]);
        }

        $responseData = $getterHelper->get($reservation, $groups);

        return $this->json($responseData);
    }

    #[Route('reservation/{reservationId}', name: 'reservation_modify', methods: ['PATCH'])]
    public function modify(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper, 
        ValidatorInterface $validator, 
        Request $request, 
        int $reservationId
        ): JsonResponse
    {

        $reservation = $entityManager->getRepository(Reservation::class)->find($reservationId);
        if(!($reservation instanceof Reservation)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => "Reservation not found"
            ]);
        }

        $schedule = $reservation->getSchedule();
        $organization = $schedule->getOrganization();
        $currentUser = $this->getUser();
        if(!($currentUser && $organization->hasMember($currentUser))){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'message' => 'Access Denied'
            ]);
        }

        $organizationMember = $organization->getMember($currentUser);
        $scheduleAssignment = $organizationMember->getScheduleAssignment($schedule);

        if(!$organizationMember->hasRoles(['ADMIN']) && !$scheduleAssignment){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'message' => 'Access Denied'
            ]);
        }

        try{
            $setterHelper->updateObjectSettings($reservation, $request->request->all(), [], ['Default']);
            $validationErrors = $setterHelper->getValidationErrors();
            
            $violations = $validator->validate($reservation, groups: $setterHelper->getValidationGroups());

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
            'message' => 'Reservation modified successfully'
        ]);
    }

    #[Route('reservation/{reservationId}', name: 'reservation_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $reservationId): JsonResponse
    {
        $reservation = $entityManager->getRepository(Reservation::class)->find($reservationId);
        if(!($reservation instanceof Reservation)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Reservation not found'
            ]);
        }

        $schedule = $reservation->getSchedule();
        $organization = $schedule->getOrganization();
        $currentUser = $this->getUser();
        if(!($currentUser && $organization->hasMember($currentUser))){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Access Denied'
            ]);
        }

        $organizationMember = $organization->getMember($currentUser);
        $scheduleAssignment = $organizationMember->getScheduleAssignment($schedule);

        if(!$organizationMember->hasRoles(['ADMIN']) && !$scheduleAssignment){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Access Denied'
            ]);
        }
        
        $entityManager->remove($reservation);
        $entityManager->flush();

        return $this->json([
            'status' => 'Success',
            'message' => 'Reservation removed successfully'
        ]);
    }

    #[Route('reservation_confirm/{reservationId}', name: 'reservation_confirm', methods: ['POST'])]
    public function confirm(EntityManagerInterface $entityManager, int $reservationId): JsonResponse
    {        
        $reservation = $entityManager->getRepository(Reservation::class)->find($reservationId);
        if(!($reservation instanceof Reservation)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Reservation not found'
            ]);
        }

        $schedule = $reservation->getSchedule();
        $organization = $schedule->getOrganization();
        $currentUser = $this->getUser();
        if(!($currentUser && $organization->hasMember($currentUser))){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Access Denied'
            ]);
        }

        $organizationMember = $organization->getMember($currentUser);
        $scheduleAssignment = $organizationMember->getScheduleAssignment($schedule);

        if(!$organizationMember->hasRoles(['ADMIN']) && !$scheduleAssignment){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Access Denied'
            ]);
        }
        
        $reservation->setConfirmed(true);
        $entityManager->flush();

        return $this->json([
            'status' => 'Success',
            'message' => 'Reservation confirmed'
        ]);
    }
}
