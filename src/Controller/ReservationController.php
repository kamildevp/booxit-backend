<?php

namespace App\Controller;

use App\Entity\EmailConfirmation;
use App\Entity\Reservation;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\MailingHelperException;
use App\Response\BadRequestResponse;
use App\Response\ForbiddenResponse;
use App\Response\NotFoundResponse;
use App\Response\ResourceCreatedResponse;
use App\Response\ServerErrorResponse;
use App\Response\SuccessResponse;
use App\Response\UnauthorizedResponse;
use App\Response\ValidationErrorResponse;
use App\Service\Auth\Attribute\RestrictedAccess;
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
                return new ValidationErrorResponse($validationErrors);
            }

            $setterHelper->runPostValidationTasks();

        }
        catch(InvalidRequestException){
            return new BadRequestResponse($setterHelper->getRequestErrors());
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
            return new ServerErrorResponse('Mailing provider error');
        }

        return new ResourceCreatedResponse($reservation);
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

            $mailingHelper->newReservationInformation($reservation, 'Reservation Verified', 'emails/reservationVerified.html.twig', true);

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

    #[RestrictedAccess]
    #[Route('reservation/{reservationId}', name: 'reservation_get', methods: ['GET'])]
    public function get(
        EntityManagerInterface $entityManager, 
        GetterHelperInterface $getterHelper,  
        Request $request, 
        int $reservationId
        ): JsonResponse
    {
        $currentUser = $this->getUser();
        if(!$currentUser){
            return new UnauthorizedResponse;
        }

        $allowedDetails = ['organization', 'schedule', 'service'];
        $details = $request->query->get('details');
        $detailGroups = !is_null($details) ? explode(',', $details) : [];
        if(!empty(array_diff($detailGroups, $allowedDetails))){
            return new BadRequestResponse(['details' => 'Requested details are invalid']);
        }

        $detailGroups = array_map(fn($group) => 'reservation-' . $group, $detailGroups);
        $groups = array_merge(['reservation'], $detailGroups);

        $reservation = $entityManager->getRepository(Reservation::class)->find($reservationId);
        if(!($reservation instanceof Reservation)){
            return new NotFoundResponse;
        }

        $schedule = $reservation->getSchedule();
        $organization = $schedule->getOrganization();
        $member = $organization->getMember($currentUser);
        $assignment = $member ? $schedule->getAssignments()->findFirst(function($key, $element) use ($member){
            return $element->getOrganizationMember() == $member;
        }) : null;

        $hasAccess = $member && ($member->hasRoles(['ADMIN']) || !is_null($assignment));
        if(!$hasAccess){
            return new ForbiddenResponse;
        }

        $responseData = $getterHelper->get($reservation, $groups);

        return new SuccessResponse($responseData);
    }

    #[RestrictedAccess]
    #[Route('reservation/{reservationId}', name: 'reservation_modify', methods: ['PATCH'])]
    public function modify(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper, 
        ValidatorInterface $validator, 
        MailingHelper $mailingHelper,
        Request $request, 
        int $reservationId
        ): JsonResponse
    {
        $currentUser = $this->getUser();
        if(!$currentUser){
            return new UnauthorizedResponse;
        }

        $reservation = $entityManager->getRepository(Reservation::class)->find($reservationId);
        if(!($reservation instanceof Reservation)){
            return new NotFoundResponse;
        }

        $schedule = $reservation->getSchedule();
        $organization = $schedule->getOrganization();
        $member = $organization->getMember($currentUser);
        $assignment = $member ? $schedule->getAssignments()->findFirst(function($key, $element) use ($member){
            return $element->getOrganizationMember() == $member;
        }) : null;

        $hasAccess = $member && ($member->hasRoles(['ADMIN']) || !is_null($assignment));
        if(!$hasAccess){
            return new ForbiddenResponse;
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
                return new ValidationErrorResponse($validationErrors);
            }

            $setterHelper->runPostValidationTasks();
        }
        catch(InvalidRequestException $e){
            return new BadRequestResponse($setterHelper->getRequestErrors());
        }

        $entityManager->flush();

        try{
            $mailingHelper->newReservationInformation($reservation, 'Reservation Modified', 'emails/reservationModified.html.twig', true);
        }
        catch(MailingHelperException){
            return new ServerErrorResponse('Mailing provider error');
        }

        return new SuccessResponse($reservation);
    }

    #[RestrictedAccess]
    #[Route('reservation/{reservationId}', name: 'reservation_delete', methods: ['DELETE'])]
    public function delete(
        EntityManagerInterface $entityManager, 
        MailingHelper $mailingHelper,
        int $reservationId
        ): JsonResponse
    {
        $currentUser = $this->getUser();
        if(!$currentUser){
            return new UnauthorizedResponse;
        }

        $reservation = $entityManager->getRepository(Reservation::class)->find($reservationId);
        if(!($reservation instanceof Reservation)){
            return new NotFoundResponse;
        }

        $schedule = $reservation->getSchedule();
        $organization = $schedule->getOrganization();
        $member = $organization->getMember($currentUser);
        $assignment = $member ? $schedule->getAssignments()->findFirst(function($key, $element) use ($member){
            return $element->getOrganizationMember() == $member;
        }) : null;

        $hasAccess = $member && ($member->hasRoles(['ADMIN']) || !is_null($assignment));
        if(!$hasAccess){
            return new ForbiddenResponse;
        }
        
        $entityManager->remove($reservation);
        $entityManager->flush();

        try{
            $mailingHelper->newReservationInformation($reservation, 'Reservation Removed', 'emails/reservationRemoved.html.twig', false); 
        }
        catch(MailingHelperException){
            return new ServerErrorResponse('Mailing provider error');
        }

        return new SuccessResponse(['message' => 'Reservation removed successfully']);
    }

    #[RestrictedAccess]
    #[Route('reservation_confirm/{reservationId}', name: 'reservation_confirm', methods: ['POST'])]
    public function confirm(EntityManagerInterface $entityManager, int $reservationId): JsonResponse
    {    
        $currentUser = $this->getUser();
        if(!$currentUser){
            return new UnauthorizedResponse;
        }

        $reservation = $entityManager->getRepository(Reservation::class)->find($reservationId);
        if(!($reservation instanceof Reservation)){
            return new NotFoundResponse;
        }

        $schedule = $reservation->getSchedule();
        $organization = $schedule->getOrganization();
        $member = $organization->getMember($currentUser);
        $assignment = $member ? $schedule->getAssignments()->findFirst(function($key, $element) use ($member){
            return $element->getOrganizationMember() == $member;
        }) : null;

        $hasAccess = $member && ($member->hasRoles(['ADMIN']) || !is_null($assignment));
        if(!$hasAccess){
            return new ForbiddenResponse;
        }
        
        $reservation->setConfirmed(true);
        $entityManager->flush();

        return new SuccessResponse($reservation);
    }
}
