<?php

declare(strict_types=1);

namespace App\Controller;

use App\Documentation\Response\ConflictResponseDoc;
use App\Documentation\Response\ForbiddenResponseDoc;
use App\Documentation\Response\NotFoundResponseDoc;
use App\Documentation\Response\PaginatorResponseDoc;
use App\Documentation\Response\ServerErrorResponseDoc;
use App\Documentation\Response\SuccessResponseDoc;
use App\Documentation\Response\UnauthorizedResponseDoc;
use App\Documentation\Response\ValidationErrorResponseDoc;
use App\DTO\Schedule\ScheduleCreateDTO;
use App\DTO\Schedule\ScheduleListQueryDTO;
use App\DTO\Schedule\SchedulePatchDTO;
use App\Entity\Organization;
use App\Entity\Schedule;
use App\Enum\Schedule\ScheduleNormalizerGroup;
use App\Repository\ScheduleRepository;
use App\Response\ResourceCreatedResponse;
use App\Response\SuccessResponse;
use App\Service\Auth\AccessRule\OrganizationManagementPrivilegesRule;
use App\Service\Auth\AccessRule\OrganizationScheduleCountRule;
use App\Service\Auth\Attribute\RestrictedAccess;
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
        </br></br>**Important:** This action can only be performed by organization administrator.'
    )]
    #[SuccessResponseDoc(
        statusCode: 201,
        description: 'Created Schedule',
        dataModel: Schedule::class,
        dataModelGroups: ScheduleNormalizerGroup::PRIVATE
    )]
    #[ConflictResponseDoc('The organization has already reached its maximum allowed number of schedules.')]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(OrganizationManagementPrivilegesRule::class)]
    #[RestrictedAccess(OrganizationScheduleCountRule::class)]
    #[Route('organizations/{organization}/schedules', name: 'schedule_new', methods: ['POST'], requirements: ['organization' => '\d+'])]
    public function create(
        Organization $organization,
        #[MapRequestPayload] ScheduleCreateDTO $dto,
        EntitySerializerInterface $entitySerializer,
        ScheduleRepository $scheduleRepository,   
    ): ResourceCreatedResponse
    {
        $schedule = $entitySerializer->parseToEntity($dto, Schedule::class);
        $schedule->setOrganization($organization);
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
    #[Route('organizations/{organization}/schedules/{schedule}', name: 'schedule_get', methods: ['GET'], requirements: ['organization' => '\d+', 'schedule' => '\d+'])]
    public function get(
        #[MapEntity(mapping:['schedule' => 'id', 'organization' => 'organization'])]Schedule $schedule, 
        EntitySerializerInterface $entitySerializer
    ): SuccessResponse
    {
        $responseData = $entitySerializer->normalize($schedule, ScheduleNormalizerGroup::ORGANIZATION_SCHEDULES->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[OA\Patch(
        summary: 'Update schedule',
        description: 'Updates schedule data.
        </br>**Important:** This action can only be performed by organization administrator.'
    )]
    #[SuccessResponseDoc(
        description: 'Updated Schedule Data',
        dataModel: Schedule::class,
        dataModelGroups: ScheduleNormalizerGroup::PRIVATE
    )]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(OrganizationManagementPrivilegesRule::class)]
    #[Route('organizations/{organization}/schedules/{schedule}', name: 'schedule_patch', methods: ['PATCH'], requirements: ['organization' => '\d+', 'schedule' => '\d+'])]
    public function patch(
        #[MapEntity(mapping:['schedule' => 'id', 'organization' => 'organization'])]Schedule $schedule, 
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
        </br>**Important:** This action can only be performed by organization administrator.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Schedule removed successfully'])]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(OrganizationManagementPrivilegesRule::class)]
    #[Route('organizations/{organization}/schedules/{schedule}', name: 'schedule_delete', methods: ['DELETE'], requirements: ['organization' => '\d+', 'schedule' => '\d+'])]
    public function delete(        
        #[MapEntity(mapping:['schedule' => 'id', 'organization' => 'organization'])]Schedule $schedule, 
        ScheduleRepository $scheduleRepository
    ): SuccessResponse
    {
        $scheduleRepository->remove($schedule, true);
        
        return new SuccessResponse(['message' => 'Schedule removed successfully']);
    }

    #[OA\Get(
        summary: 'List schedules',
        description: 'Retrieves a paginated list of schedules for specified organization'
    )]
    #[PaginatorResponseDoc(
        description: 'Paginated list of schedules', 
        dataModel: Schedule::class,
        dataModelGroups: ScheduleNormalizerGroup::ORGANIZATION_SCHEDULES
    )]
    #[NotFoundResponseDoc('Organization not found')]
    #[ValidationErrorResponseDoc]
    #[Route('organizations/{organization}/schedules', name: 'schedule_list', methods: ['GET'], requirements: ['organization' => '\d+'])]
    public function list(
        Organization $organization,
        EntitySerializerInterface $entitySerializer, 
        ScheduleRepository $scheduleRepository, 
        #[MapQueryString] ScheduleListQueryDTO $queryDTO = new ScheduleListQueryDTO,
    ): SuccessResponse
    {
        $paginationResult = $scheduleRepository->paginateRelatedTo(
            $queryDTO, 
            ['organization' => $organization]
        );
        $result = $entitySerializer->normalizePaginationResult($paginationResult, ScheduleNormalizerGroup::ORGANIZATION_SCHEDULES->normalizationGroups());

        return new SuccessResponse($result);
    }
}