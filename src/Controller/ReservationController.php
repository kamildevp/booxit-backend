<?php

namespace App\Controller;

use App\Documentation\Response\ConflictResponseDoc;
use App\Documentation\Response\ServerErrorResponseDoc;
use App\Documentation\Response\SuccessResponseDoc;
use App\Documentation\Response\ValidationErrorResponseDoc;
use App\DTO\Reservation\ReservationUrlCancelDTO;
use App\DTO\Reservation\ReservationVerifyDTO;
use App\Response\ApiResponse;
use App\Response\SuccessResponse;
use App\Response\ValidationFailedResponse;
use App\Service\Entity\ReservationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[ServerErrorResponseDoc]
#[OA\Tag('Reservation')]
class ReservationController extends AbstractController
{
    #[OA\Post(
        summary: 'Verify reservation',
        description: 'Verifies reservation using the verification parameters provided in the link sent to reservation email address. 
        This endpoint should be called by the verification handler specified during reservation creation.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Verification Successful'])]
    #[ConflictResponseDoc('Corresponding reservation does not exist, have been cancelled or is already verified.')]
    #[ValidationErrorResponseDoc]
    #[Route('reservations/verify', name: 'reservation_verify', methods: ['POST'])]
    public function verify(ReservationService $reservationService, #[MapRequestPayload] ReservationVerifyDTO $dto): ApiResponse
    {
        $verified = $reservationService->verifyReservation($dto);

        return $verified ? 
            new SuccessResponse(['message' => 'Verification Successful']) : 
            new ValidationFailedResponse('Verification Failed');
    }

    #[OA\Post(
        summary: 'Cancel reservation using cancellation link parameters',
        description: 'Cancels reservation based on valid cancellation link parameters. 
        This endpoint should be called by the verification handler specified during reservation creation.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Verification Successful'])]
    #[ConflictResponseDoc('Corresponding reservation does not exist or already has been cancelled.')]
    #[ValidationErrorResponseDoc]
    #[Route('reservations/url-cancel', name: 'reservation_url_cancel', methods: ['POST'])]
    public function cancelByUrl(ReservationService $reservationService, #[MapRequestPayload] ReservationUrlCancelDTO $dto): ApiResponse
    {
        $verified = $reservationService->cancelReservationByUrl($dto);

        return $verified ? 
            new SuccessResponse(['message' => 'Reservation has been cancelled']) : 
            new ValidationFailedResponse('Verification Failed');
    }
}
