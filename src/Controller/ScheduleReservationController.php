<?php

namespace App\Controller;

use App\Documentation\Response\ConflictResponseDoc;
use App\Documentation\Response\ForbiddenResponseDoc;
use App\Documentation\Response\NotFoundResponseDoc;
use App\Documentation\Response\PaginatorResponseDoc;
use App\Documentation\Response\ServerErrorResponseDoc;
use App\Documentation\Response\SuccessResponseDoc;
use App\Documentation\Response\UnauthorizedResponseDoc;
use App\Documentation\Response\ValidationErrorResponseDoc;
use App\DTO\ScheduleReservation\ScheduleReservationConfirmDTO;
use App\DTO\ScheduleReservation\ScheduleReservationCreateCustomDTO;
use App\DTO\ScheduleReservation\ScheduleReservationCreateDTO;
use App\DTO\ScheduleReservation\ScheduleReservationListQueryDTO;
use App\DTO\ScheduleReservation\ScheduleReservationPatchDTO;
use App\Entity\Reservation;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Entity\User;
use App\Enum\Reservation\ReservationNormalizerGroup;
use App\Repository\ReservationRepository;
use App\Response\ResourceCreatedResponse;
use App\Response\SuccessResponse;
use App\Service\Auth\AccessRule\ScheduleReadPrivilegesRule;
use App\Service\Auth\AccessRule\ScheduleWritePrivilegesRule;
use App\Service\Auth\Attribute\RestrictedAccess;
use App\Service\Entity\ScheduleReservationService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

#[ServerErrorResponseDoc]
#[OA\Tag('ScheduleReservation')]
class ScheduleReservationController extends AbstractController
{
    #[OA\Post(
        summary: 'Create a new schedule reservation',
        description: 'Creates a new schedule reservation and sends an email containing verification and cancellation link. 
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
    #[ConflictResponseDoc('Reservation time slot is not available.')]
    #[ValidationErrorResponseDoc]
    #[Route('schedules/{schedule}/reservations', name: 'schedule_reservation_new', methods: ['POST'], requirements: ['schedule' => '\d+'])]
    public function create(
        Schedule $schedule,
        ScheduleReservationService $scheduleReservationService, 
        #[MapRequestPayload] ScheduleReservationCreateDTO $dto,
        EntitySerializerInterface $entitySerializer,   
    ): ResourceCreatedResponse
    {
        $reservation = $scheduleReservationService->createScheduleReservation($schedule, $dto);
        $responseData = $entitySerializer->normalize($reservation, ReservationNormalizerGroup::USER_RESERVATIONS->normalizationGroups());
        
        return new ResourceCreatedResponse($responseData);
    }

    #[OA\Post(
        summary: 'Create custom schedule reservation',
        description: 'Creates custom schedule reservation. 
        </br><br>**Important:** This action can only be performed by organization admin or schedule assignee with *WRITE* privileges.'
    )]
    #[SuccessResponseDoc(
        statusCode: 201,
        description: 'Created Reservation',
        dataModel: Reservation::class,
        dataModelGroups: ReservationNormalizerGroup::SCHEDULE_RESERVATIONS
    )]
    #[NotFoundResponseDoc('Reservation not found')]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ScheduleWritePrivilegesRule::class)]
    #[Route('schedules/{schedule}/reservations/custom', name: 'schedule_reservation_create_custom', methods: ['POST'], requirements: ['schedule' => '\d+'])]
    public function createCustom(
        Schedule $schedule,
        ScheduleReservationService $scheduleReservationService,
        EntitySerializerInterface $entitySerializer,   
        #[MapRequestPayload] ScheduleReservationCreateCustomDTO $dto,
    ): ResourceCreatedResponse
    {
        $reservation = $scheduleReservationService->createCustomScheduleReservation($schedule, $dto);
        $responseData = $entitySerializer->normalize($reservation, ReservationNormalizerGroup::SCHEDULE_RESERVATIONS->normalizationGroups());

        return new ResourceCreatedResponse($responseData);
    }

    #[OA\Post(
        summary: 'Cancel schedule reservation',
        description: 'Cancels specified schedule reservation and sends reservation cancellation email to customer. 
        </br><br>**Important:** This action can only be performed by organization admin or schedule assignee with *WRITE* privileges.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Reservation has been cancelled'])]
    #[NotFoundResponseDoc('Reservation not found')]
    #[ConflictResponseDoc('Reservation has already been cancelled.')]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ScheduleWritePrivilegesRule::class)]
    #[Route('schedules/{schedule}/reservations/{reservation}/cancel', name: 'schedule_reservation_cancel', methods: ['POST'], requirements: ['schedule' => '\d+', 'reservation' => '\d+'])]
    public function cancel(
        #[MapEntity(mapping: ['reservation' => 'id', 'schedule' => 'schedule'])]Reservation $reservation,
        ScheduleReservationService $scheduleReservationService,
    ): SuccessResponse
    {
        $scheduleReservationService->cancelScheduleReservation($reservation);

        return new SuccessResponse(['message' => 'Reservation has been cancelled']);
    }

    #[OA\Post(
        summary: 'Confirm schedule reservation',
        description: 'Confirms specified schedule reservation and sends reservation confirmation email with cancellation link. 
        The link is generated using the specified **verification_handler**, which must match one of the predefined handlers to ensure it points to a trusted domain. 
        <br><br>**Note:** The *"internal"* verification handler is a dummy handler used to generate a safe verification URL when no external handler is provided. 
        To complete the cancellation process, the appropriate cancellation endpoint must be called with the parameters extracted from the decoded cancellation link.
        </br><br>**Important:** This action can only be performed by organization admin or schedule assignee with *WRITE* privileges.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Reservation has been confirmed'])]
    #[NotFoundResponseDoc('Reservation not found')]
    #[ConflictResponseDoc('Reservation has already been confirmed.')]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ScheduleWritePrivilegesRule::class)]
    #[Route('schedules/{schedule}/reservations/{reservation}/confirm', name: 'schedule_reservation_confirm', methods: ['POST'], requirements: ['schedule' => '\d+', 'reservation' => '\d+'])]
    public function confirm(
        #[MapEntity(mapping: ['reservation' => 'id', 'schedule' => 'schedule'])]Reservation $reservation,
        ScheduleReservationService $scheduleReservationService,
        #[MapRequestPayload] ScheduleReservationConfirmDTO $dto,
    ): SuccessResponse
    {
        $scheduleReservationService->confirmScheduleReservation($reservation, $dto);

        return new SuccessResponse(['message' => 'Reservation has been confirmed']);
    }

    #[OA\Get(
        summary: 'Get schedule reservation',
        description: 'Returns organization-only data of the specified schedule reservation.
        </br><br>**Important:** This endpoint can only be accessed by organization admin or reservation schedule assignee.'
    )]
    #[SuccessResponseDoc(
        description: 'Requested Reservation Data',
        dataModel: Reservation::class,
        dataModelGroups: ReservationNormalizerGroup::SCHEDULE_RESERVATIONS
    )]
    #[NotFoundResponseDoc('Reservation not found')]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ScheduleReadPrivilegesRule::class)]
    #[Route('schedules/{schedule}/reservations/{reservation}', name: 'schedule_reservation_get', methods: ['GET'], requirements: ['schedule' => '\d+', 'reservation' => '\d+'])]
    public function get(
        #[MapEntity(mapping: ['reservation' => 'id', 'schedule' => 'schedule'])]Reservation $reservation,
        EntitySerializerInterface $entitySerializer
    ): SuccessResponse
    {
        $responseData = $entitySerializer->normalize($reservation, ReservationNormalizerGroup::SCHEDULE_RESERVATIONS->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[OA\Patch(
        summary: 'Update schedule reservation',
        description: 'Updates schedule reservation data.
        </br><br>**Important:** This action can only be performed by organization admin or reservation schedule assignee with *WRITE* privileges.'
    )]
    #[SuccessResponseDoc(
        description: 'Updated Reservation Data',
        dataModel: Reservation::class,
        dataModelGroups: ReservationNormalizerGroup::SCHEDULE_RESERVATIONS
    )]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ScheduleWritePrivilegesRule::class)]
    #[Route('schedules/{schedule}/reservations/{reservation}', name: 'schedule_reservation_patch', methods: ['PATCH'], requirements: ['schedule' => '\d+', 'reservation' => '\d+'])]
    public function patch(
        #[MapEntity(mapping: ['reservation' => 'id', 'schedule' => 'schedule'])]Reservation $reservation,
        ScheduleReservationService $scheduleReservationService,
        EntitySerializerInterface $entitySerializer, 
        #[MapRequestPayload] ScheduleReservationPatchDTO $dto,
    ): SuccessResponse
    {
        $reservation = $scheduleReservationService->patchScheduleReservation($reservation, $dto);
        $responseData = $entitySerializer->normalize($reservation, ReservationNormalizerGroup::ORGANIZATION_RESERVATIONS->normalizationGroups());
        
        return new SuccessResponse($responseData);
    }

    #[OA\Delete(
        summary: 'Delete reservation',
        description: 'Deletes the specified reservation.
        </br><br>**Important:** This action can only be performed by organization admin or reservation schedule assignee with *WRITE* privileges.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Reservation has been removed'])]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ScheduleWritePrivilegesRule::class)]
    #[Route('schedules/{schedule}/reservations/{reservation}', name: 'schedule_reservation_delete', methods: ['DELETE'], requirements: ['schedule' => '\d+', 'reservation' => '\d+'])]
    public function delete(        
        #[MapEntity(mapping: ['reservation' => 'id', 'schedule' => 'schedule'])]Reservation $reservation,
        ReservationRepository $reservationRepository,
    ): SuccessResponse
    {
        $reservationRepository->remove($reservation, true);
        
        return new SuccessResponse(['message' => 'Reservation has been removed']);
    }

    #[OA\Get(
        summary: 'List schedule reservations',
        description: 'Retrieves a paginated list of schedule reservations.
        </br><br>**Important:** This endpoint can only be accessed by organization admin or reservation schedule assignee.'
    )]
    #[PaginatorResponseDoc(
        description: 'Paginated reservations list', 
        dataModel: Reservation::class,
        dataModelGroups: ReservationNormalizerGroup::SCHEDULE_RESERVATIONS
    )]
    #[ValidationErrorResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess]
    #[Route('schedules/{schedule}/reservations', name: 'schedule_reservations_list', methods: ['GET'], requirements: ['schedule' => '\d+'])]
    public function list(
        Schedule $schedule,
        ReservationRepository $reservationRepository, 
        EntitySerializerInterface $entitySerializer, 
        #[MapQueryString]ScheduleReservationListQueryDTO $queryDTO = new ScheduleReservationListQueryDTO,
    ): SuccessResponse
    {
        $paginationResult = $reservationRepository->paginateRelatedTo(
            $queryDTO, 
            ['schedule' => $schedule],
            [
                'service' => Service::class,
                'reservedBy' => User::class,
            ]
        );
        $result = $entitySerializer->normalizePaginationResult($paginationResult, ReservationNormalizerGroup::SCHEDULE_RESERVATIONS->normalizationGroups());

        return new SuccessResponse($result);
    }
}
