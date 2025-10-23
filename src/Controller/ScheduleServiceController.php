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
use App\DTO\ScheduleService\ScheduleServiceAvailabilityGetDTO;
use App\DTO\ScheduleService\ScheduleServiceAddDTO;
use App\DTO\Service\ServiceListQueryDTO;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Enum\Service\ServiceNormalizerGroup;
use App\Repository\ServiceRepository;
use App\Response\SuccessResponse;
use App\Service\Auth\AccessRule\ScheduleWritePrivilegesRule;
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
#[OA\Tag('ScheduleService')]
class ScheduleServiceController extends AbstractController
{
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
    #[RestrictedAccess(ScheduleWritePrivilegesRule::class)]
    #[Route('schedules/{schedule}/services', name: 'schedule_service_add', methods: ['POST'], requirements: ['schedule' => '\d+'])]
    public function add(
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
    #[RestrictedAccess(ScheduleWritePrivilegesRule::class)]
    #[Route('schedules/{schedule}/services/{service}', name: 'schedule_service_delete', methods: ['DELETE'], requirements: ['schedule' => '\d+', 'service' => '\d+'])]
    public function delete(  
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
    public function list(
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
        #[MapEntity(mapping:['service' => 'id', 'schedule' => 'schedule'])]Service $service, 
        ScheduleService $scheduleService,
        #[MapQueryString] ScheduleServiceAvailabilityGetDTO $dto = new ScheduleServiceAvailabilityGetDTO
    ): SuccessResponse
    {
        $availability = $scheduleService->getScheduleAvailability($schedule, $service, $dto);

        return new SuccessResponse($availability);
    }
}