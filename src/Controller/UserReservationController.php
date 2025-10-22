<?php

namespace App\Controller;

use App\Documentation\Response\ConflictResponseDoc;
use App\Documentation\Response\NotFoundResponseDoc;
use App\Documentation\Response\ServerErrorResponseDoc;
use App\Documentation\Response\SuccessResponseDoc;
use App\Documentation\Response\UnauthorizedResponseDoc;
use App\Documentation\Response\ValidationErrorResponseDoc;
use App\DTO\UserReservation\UserReservationCreateDTO;
use App\Entity\Reservation;
use App\Enum\Reservation\ReservationNormalizerGroup;
use App\Response\ResourceCreatedResponse;
use App\Response\SuccessResponse;
use App\Service\Auth\Attribute\RestrictedAccess;
use App\Service\Entity\ReservationService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[ServerErrorResponseDoc]
#[OA\Tag('UserReservation')]
class UserReservationController extends AbstractController
{
    #[OA\Post(
        summary: 'Create a new reservation for user account',
        description: 'Creates a new reservation, links it to logged in user account and sends reservation summary email containing reservation cancellation link. 
        The link is generated using the specified **verification_handler**, which must match one of the predefined handlers to ensure it points to a trusted domain. 
        <br><br>**Note:** The *"internal"* verification handler is a dummy handler used to generate a safe verification URL when no external handler is provided. 
        To complete the cancellation process, the appropriate cancellation endpoint must be called with the parameters extracted from the decoded cancellation link.'
    )]
    #[SuccessResponseDoc(
        statusCode: 201,
        description: 'Created Reservation',
        dataModel: Reservation::class,
        dataModelGroups: ReservationNormalizerGroup::USER_RESERVATIONS
    )]
    #[ConflictResponseDoc('Reservation time slot in not available.')]
    #[ValidationErrorResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess]
    #[Route('user/me/reservations', name: 'user_reservation_new', methods: ['POST'])]
    public function create(
        ReservationService $reservationService, 
        #[MapRequestPayload] UserReservationCreateDTO $dto,
        EntitySerializerInterface $entitySerializer,   
    ): ResourceCreatedResponse
    {
        $reservation = $reservationService->createUserReservation($dto, $this->getUser());
        $responseData = $entitySerializer->normalize($reservation, ReservationNormalizerGroup::USER_RESERVATIONS->normalizationGroups());
        
        return new ResourceCreatedResponse($responseData);
    }

    #[OA\Post(
        summary: 'Cancel user reservation',
        description: 'Cancels specified reservation linked to user account and sends reservation cancellation email.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Reservation has been cancelled'])]
    #[NotFoundResponseDoc('Reservation not found')]
    #[ConflictResponseDoc('Reservation has already been cancelled.')]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess]
    #[Route('users/me/reservations/{reservation}/cancel', name: 'user_reservation_cancel', methods: ['POST'], requirements: ['reservation' => '\d+'])]
    public function cancel(
        Reservation $reservation,
        ReservationService $reservationService,
    ): SuccessResponse
    {
        $reservationService->cancelUserReservation($reservation, $this->getUser());

        return new SuccessResponse(['message' => 'Reservation has been cancelled']);
    }
}
