<?php

namespace App\Controller;

use App\Documentation\Response\ConflictResponseDoc;
use App\Documentation\Response\ServerErrorResponseDoc;
use App\Documentation\Response\SuccessResponseDoc;
use App\Documentation\Response\UnauthorizedResponseDoc;
use App\Documentation\Response\ValidationErrorResponseDoc;
use App\DTO\Reservation\ReservationCreateDTO;
use App\DTO\Reservation\ReservationVerifyDTO;
use App\DTO\Reservation\UserReservationCreateDTO;
use App\Entity\Reservation;
use App\Enum\Reservation\ReservationNormalizerGroup;
use App\Response\ApiResponse;
use App\Response\ResourceCreatedResponse;
use App\Response\SuccessResponse;
use App\Response\ValidationFailedResponse;
use App\Service\Auth\Attribute\RestrictedAccess;
use App\Service\Entity\ReservationService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[ServerErrorResponseDoc]
#[OA\Tag('Reservation')]
class ReservationController extends AbstractController
{
    #[OA\Post(
        summary: 'Create a new reservation',
        description: 'Creates a new reservation and sends an email containing verification and cancellation link. 
        The link is generated using the specified **verification_handler**, which must match one of the predefined handlers to ensure it points to a trusted domain. 
        Unverified reservations are automatically deleted after 30 minutes.
        <br><br>**Note:** The *"internal"* verification handler is a dummy handler used to generate a safe verification URL when no external handler is provided. 
        To complete the verification process, the appropriate verification endpoint must be called with the parameters extracted from the decoded verification link.'
    )]
    #[SuccessResponseDoc(
        statusCode: 201,
        description: 'Created Reservation',
        dataModel: Reservation::class,
        dataModelGroups: ReservationNormalizerGroup::USER_RESERVATIONS
    )]
    #[ConflictResponseDoc('Reservation time slot in not available.')]
    #[ValidationErrorResponseDoc]
    #[Route('reservations', name: 'reservation_new', methods: ['POST'])]
    public function create(
        ReservationService $reservationService, 
        #[MapRequestPayload] ReservationCreateDTO $dto,
        EntitySerializerInterface $entitySerializer,   
    ): ResourceCreatedResponse
    {
        $reservation = $reservationService->createReservation($dto);
        $responseData = $entitySerializer->normalize($reservation, ReservationNormalizerGroup::USER_RESERVATIONS->normalizationGroups());
        
        return new ResourceCreatedResponse($responseData);
    }

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
    #[Route('reservations/me', name: 'reservation_new_user_reservation', methods: ['POST'])]
    public function createUserReservation(
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
        summary: 'Verify reservation',
        description: 'Verifies reservation using the verification parameters provided in the link sent to reservation email address. 
        This endpoint should be called by the verification handler specified during reservation creation.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Verification Successful'])]
    #[ValidationErrorResponseDoc]
    #[Route('reservations/verify', name: 'reservation_verify', methods: ['POST'])]
    public function verify(ReservationService $reservationService, #[MapRequestPayload] ReservationVerifyDTO $dto): ApiResponse
    {
        $verified = $reservationService->verifyReservation($dto);

        return $verified ? 
            new SuccessResponse(['message' => 'Verification Successful']) : 
            new ValidationFailedResponse('Verification Failed');
    }
}
