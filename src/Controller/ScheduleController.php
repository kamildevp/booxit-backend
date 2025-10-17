<?php

declare(strict_types=1);

namespace App\Controller;

use App\Documentation\Response\AvailabilityResponseDoc;
use App\Documentation\Response\ConflictResponseDoc;
use App\Documentation\Response\ForbiddenResponseDoc;
use App\Documentation\Response\NotFoundResponseDoc;
use App\Documentation\Response\PaginatorResponseDoc;
use App\Documentation\Response\ServerErrorResponseDoc;
use App\Documentation\Response\SuccessResponseDoc;
use App\Documentation\Response\UnauthorizedResponseDoc;
use App\Documentation\Response\ValidationErrorResponseDoc;
use App\DTO\Schedule\ScheduleAvailabilityGetDTO;
use App\DTO\Schedule\ScheduleCreateDTO;
use App\DTO\Schedule\ScheduleListQueryDTO;
use App\DTO\Schedule\SchedulePatchDTO;
use App\DTO\Schedule\ScheduleServiceAddDTO;
use App\DTO\Service\ServiceListQueryDTO;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Enum\Schedule\ScheduleNormalizerGroup;
use App\Enum\Service\ServiceNormalizerGroup;
use App\Repository\ScheduleRepository;
use App\Repository\ServiceRepository;
use App\Response\ResourceCreatedResponse;
use App\Response\SuccessResponse;
use App\Service\Auth\AccessRule\ScheduleManagementPrivilegesRule;
use App\Service\Auth\Attribute\RestrictedAccess;
use App\Service\Entity\ScheduleService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[ServerErrorResponseDoc]
#[OA\Tag('Schedule')]
class ScheduleController extends AbstractController
{
    #[OA\Post(
        summary: 'Create a new schedule',
        description: 'Creates a new schedule for specified organization.
        </br></br>**Important:** This action can only be performed by organization admin.'
    )]
    #[SuccessResponseDoc(
        statusCode: 201,
        description: 'Created Schedule',
        dataModel: Schedule::class,
        dataModelGroups: ScheduleNormalizerGroup::PRIVATE
    )]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ScheduleManagementPrivilegesRule::class)]
    #[Route('schedules', name: 'schedule_new', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] ScheduleCreateDTO $dto,
        EntitySerializerInterface $entitySerializer,
        ScheduleRepository $scheduleRepository,   
    ): ResourceCreatedResponse
    {
        $schedule = $entitySerializer->parseToEntity($dto, Schedule::class);
        $scheduleRepository->save($schedule, true);
        $responseData = $entitySerializer->normalize($schedule, ScheduleNormalizerGroup::PRIVATE->normalizationGroups());
        
        return new ResourceCreatedResponse($responseData);
    }

    #[OA\Get(
        summary: 'Get schedule',
        description: 'Returns the public data of the specified schedule.'
    )]
    #[SuccessResponseDoc(
        description: 'Requested Schedule Data',
        dataModel: Schedule::class,
        dataModelGroups: ScheduleNormalizerGroup::PUBLIC
    )]
    #[NotFoundResponseDoc('Schedule not found')]
    #[Route('schedules/{schedule}', name: 'schedule_get', methods: ['GET'], requirements: ['schedule' => '\d+'])]
    public function get(Schedule $schedule, EntitySerializerInterface $entitySerializer): SuccessResponse
    {
        $responseData = $entitySerializer->normalize($schedule, ScheduleNormalizerGroup::PUBLIC->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[OA\Patch(
        summary: 'Update schedule',
        description: 'Updates schedule data.
        </br>**Important:** This action can only be performed by organization admin.'
    )]
    #[SuccessResponseDoc(
        description: 'Updated Schedule Data',
        dataModel: Schedule::class,
        dataModelGroups: ScheduleNormalizerGroup::PRIVATE
    )]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ScheduleManagementPrivilegesRule::class)]
    #[Route('schedules/{schedule}', name: 'schedule_patch', methods: ['PATCH'], requirements: ['schedule' => '\d+'])]
    public function patch(
        Schedule $schedule, 
        EntitySerializerInterface $entitySerializer, 
        ScheduleRepository $scheduleRepository,
        #[MapRequestPayload] SchedulePatchDTO $dto,
    ): SuccessResponse
    {
        $schedule = $entitySerializer->parseToEntity($dto, $schedule);
        $scheduleRepository->save($schedule, true);
        $responseData = $entitySerializer->normalize($schedule, ScheduleNormalizerGroup::PRIVATE->normalizationGroups());
        
        return new SuccessResponse($responseData);
    }

    #[OA\Delete(
        summary: 'Delete schedule',
        description: 'Deletes the specified schedule.
        </br>**Important:** This action can only be performed by organization admin.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Schedule removed successfully'])]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ScheduleManagementPrivilegesRule::class)]
    #[Route('schedules/{schedule}', name: 'schedule_delete', methods: ['DELETE'], requirements: ['schedule' => '\d+'])]
    public function delete(        
        Schedule $schedule, 
        ScheduleRepository $scheduleRepository
    ): SuccessResponse
    {
        $scheduleRepository->remove($schedule, true);
        
        return new SuccessResponse(['message' => 'Schedule removed successfully']);
    }

    #[OA\Get(
        summary: 'List schedules',
        description: 'Retrieves a paginated list of existing schedules with their public information.'
    )]
    #[PaginatorResponseDoc(
        description: 'Paginated users list', 
        dataModel: Schedule::class,
        dataModelGroups: ScheduleNormalizerGroup::PUBLIC
    )]
    #[ValidationErrorResponseDoc]
    #[Route('schedules', name: 'schedule_list', methods: ['GET'])]
    public function list(
        ScheduleRepository $scheduleRepository, 
        EntitySerializerInterface $entitySerializer, 
        #[MapQueryString] ScheduleListQueryDTO $queryDTO = new ScheduleListQueryDTO,
    ): SuccessResponse
    {
        $paginationResult = $scheduleRepository->paginate($queryDTO);
        $result = $entitySerializer->normalizePaginationResult($paginationResult, ScheduleNormalizerGroup::PUBLIC->normalizationGroups());

        return new SuccessResponse($result);
    }

    #[OA\Post(
        summary: 'Add new schedule service',
        description: 'Adds new schedule service.
        </br>**Important:** This action can only be performed by the organization administrator'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Service has been added to the schedule'])]
    #[NotFoundResponseDoc('Schedule not found')]
    #[ConflictResponseDoc('This service is already assigned to this schedule.')]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ScheduleManagementPrivilegesRule::class)]
    #[Route('schedules/{schedule}/services', name: 'schedule_service_add', methods: ['POST'], requirements: ['schedule' => '\d+'])]
    public function addService(
        Schedule $schedule,
        ScheduleService $scheduleService,
        #[MapRequestPayload] ScheduleServiceAddDTO $dto,
    ): SuccessResponse
    {
        $scheduleService->addScheduleService($schedule, $dto->serviceId);
        return new SuccessResponse(['message' => 'Service has been added to the schedule']);
    }

    #[OA\Delete(
        summary: 'Remove schedule service',
        description: 'Deletes the specified schedule service.
        </br>**Important:** This action can only be performed by organization admin.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Service has been removed from schedule'])]
    #[NotFoundResponseDoc('Schedule not found')]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ScheduleManagementPrivilegesRule::class)]
    #[Route('schedules/{schedule}/services/{service}', name: 'schedule_service_delete', methods: ['DELETE'], requirements: ['schedule' => '\d+', 'service' => '\d+'])]
    public function deleteService(        
        Schedule $schedule, 
        Service $service, 
        ScheduleService $scheduleService
    ): SuccessResponse
    {
        $scheduleService->removeScheduleService($schedule, $service);
        
        return new SuccessResponse(['message' => 'Service has been removed from schedule']);
    }

    #[OA\Get(
        summary: 'List schedule services',
        description: 'Retrieves a paginated list of schedule services.'
    )]
    #[PaginatorResponseDoc(
        description: 'Paginated schedule services list', 
        dataModel: Service::class,
        dataModelGroups: ServiceNormalizerGroup::ORGANIZATION_SERVICES
    )]
    #[NotFoundResponseDoc('Schedule not found')]
    #[ValidationErrorResponseDoc]
    #[Route('schedules/{schedule}/services', name: 'schedule_service_list', methods: ['GET'], requirements: ['schedule' => '\d+'])]
    public function listServices(
        Schedule $schedule,
        EntitySerializerInterface $entitySerializer, 
        ServiceRepository $serviceRepository, 
        #[MapQueryString] ServiceListQueryDTO $queryDTO = new ServiceListQueryDTO,
    ): SuccessResponse
    {
        $paginationResult = $serviceRepository->paginateRelatedTo(
            $queryDTO, 
            ['schedules' => $schedule],
        );
        $result = $entitySerializer->normalizePaginationResult($paginationResult, ServiceNormalizerGroup::ORGANIZATION_SERVICES->normalizationGroups());

        return new SuccessResponse($result);
    }

    #[OA\Get(
        summary: 'Get schedule service availability',
        description: 'Returns schedule service availability for the specified date range (up to one month). If no range is provided, the current week is used by default.
        </br></br>**Note:** Availability is returned in the Europe/Warsaw timezone.'
    )]
    #[AvailabilityResponseDoc]
    #[ValidationErrorResponseDoc]
    #[NotFoundResponseDoc('Schedule not found')]
    #[Route('schedules/{schedule}/services/{service}/availability', name: 'schedule_availability_get', methods: ['GET'], requirements: ['schedule' => '\d+', 'service' => '\d+'])]
    public function getAvailability(
        Schedule $schedule, 
        Service $service, 
        ScheduleService $scheduleService,
        #[MapQueryString] ScheduleAvailabilityGetDTO $dto = new ScheduleAvailabilityGetDTO
    ): SuccessResponse
    {
        $availability = $scheduleService->getScheduleAvailability($schedule, $service, $dto);

        return new SuccessResponse($availability);
    }
}