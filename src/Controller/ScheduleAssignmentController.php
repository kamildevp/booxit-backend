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
use App\DTO\ScheduleAssignment\ScheduleAssignmentCreateDTO;
use App\DTO\ScheduleAssignment\ScheduleAssignmentListQueryDTO;
use App\DTO\ScheduleAssignment\ScheduleAssignmentPatchDTO;
use App\Entity\OrganizationMember;
use App\Entity\Schedule;
use App\Entity\ScheduleAssignment;
use App\Entity\User;
use App\Enum\Schedule\ScheduleAccessType;
use App\Enum\ScheduleAssignment\ScheduleAssignmentNormalizerGroup;
use App\Repository\ScheduleAssignmentRepository;
use App\Response\SuccessResponse;
use App\Service\Auth\AccessRule\ScheduleManagementPrivilegesRule;
use App\Service\Auth\Attribute\RestrictedAccess;
use App\Service\Entity\ScheduleAssignmentService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

#[ForbiddenResponseDoc]
#[UnauthorizedResponseDoc]
#[ServerErrorResponseDoc]
#[OA\Tag('ScheduleAssignment')]
#[RestrictedAccess(ScheduleManagementPrivilegesRule::class)]
class ScheduleAssignmentController extends AbstractController
{
    #[OA\Post(
        summary: 'Add new schedule assignment',
        description: 'Adds new schedule assignment with specified access type.
        </br>**Important:** This action can only be performed by the organization administrator'
    )]
    #[SuccessResponseDoc(
        statusCode: 200,
        description: 'Added ScheduleAssignment',
        dataModel: ScheduleAssignment::class,
        dataModelGroups: ScheduleAssignmentNormalizerGroup::PRIVATE
    )]
    #[NotFoundResponseDoc('Schedule not found')]
    #[ConflictResponseDoc('This organization member is already assigned to this schedule.')]
    #[ValidationErrorResponseDoc]
    #[Route('schedules/{schedule}/assignments', name: 'schedule_assignment_create', methods: ['POST'], requirements: ['schedule' => '\d+'])]
    public function create(
        Schedule $schedule,
        #[MapRequestPayload] ScheduleAssignmentCreateDTO $dto,
        EntitySerializerInterface $entitySerializer,
        ScheduleAssignmentService $scheduleAssignmentService,
    ): SuccessResponse
    {
        $scheduleAssignment = $scheduleAssignmentService->createScheduleAssignment($schedule, $dto->organizationMemberId, ScheduleAccessType::from($dto->accessType));
        $responseData = $entitySerializer->normalize($scheduleAssignment, ScheduleAssignmentNormalizerGroup::PRIVATE->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[OA\Get(
        summary: 'List schedule assignments',
        description: 'Retrieves a paginated list of schedule assignments.
        </br>**Important:** This action can only be performed by the organization administrator'
    )]
    #[PaginatorResponseDoc(
        description: 'Paginated schedule assignments list', 
        dataModel: ScheduleAssignment::class,
        dataModelGroups: ScheduleAssignmentNormalizerGroup::PUBLIC
    )]
    #[NotFoundResponseDoc('Schedule not found')]
    #[ValidationErrorResponseDoc]
    #[Route('schedules/{schedule}/assignments', name: 'schedule_assignment_list', methods: ['GET'], requirements: ['schedule' => '\d+'])]
    public function list(
        Schedule $schedule,
        EntitySerializerInterface $entitySerializer, 
        ScheduleAssignmentRepository $scheduleAssignmentRepository, 
        #[MapQueryString] ScheduleAssignmentListQueryDTO $queryDTO = new ScheduleAssignmentListQueryDTO,
    ): SuccessResponse
    {
        $paginationResult = $scheduleAssignmentRepository->paginateRelatedTo(
            $queryDTO, 
            ['schedule' => $schedule], 
            [
                'organizationMember' => [
                    OrganizationMember::class,
                    ['appUser' => User::class]
                ]
            ]
        );
        $result = $entitySerializer->normalizePaginationResult($paginationResult, ScheduleAssignmentNormalizerGroup::PUBLIC->normalizationGroups());

        return new SuccessResponse($result);
    }

    #[OA\Get(
        summary: 'Get schedule assignment',
        description: 'Returns the public data of the specified schedule assignment.
        </br>**Important:** This action can only be performed by the organization administrator'
    )]
    #[SuccessResponseDoc(
        description: 'Requested ScheduleAssignment Data',
        dataModel: ScheduleAssignment::class,
        dataModelGroups: ScheduleAssignmentNormalizerGroup::PUBLIC
    )]
    #[NotFoundResponseDoc('ScheduleAssignment not found')]
    #[Route('schedules/{schedule}/assignments/{scheduleAssignment}', name: 'schedule_assignment_get', methods: ['GET'], requirements: ['schedule' => '\d+', 'scheduleAssignment' => '\d+'])]
    public function get(
        #[MapEntity(mapping:['schedule' => 'schedule', 'scheduleAssignment' => 'id'])] ScheduleAssignment $scheduleAssignment, 
        EntitySerializerInterface $entitySerializer
    ): SuccessResponse
    {
        $responseData = $entitySerializer->normalize($scheduleAssignment, ScheduleAssignmentNormalizerGroup::PUBLIC->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[OA\Patch(
        summary: 'Update schedule assignment',
        description: 'Updates schedule assignment data.
        </br>**Important:** This action can only be performed by the organization administrator.'
    )]
    #[SuccessResponseDoc(
        description: 'Updated ScheduleAssignment Data',
        dataModel: ScheduleAssignment::class,
        dataModelGroups: ScheduleAssignmentNormalizerGroup::PUBLIC
    )]
    #[NotFoundResponseDoc('ScheduleAssignment not found')]
    #[ValidationErrorResponseDoc]
    #[Route('schedules/{schedule}/assignments/{scheduleAssignment}', name: 'schedule_assignment_patch', methods: ['PATCH'], requirements: ['schedule' => '\d+', 'scheduleAssignment' => '\d+'])]
    public function patch(
        #[MapEntity(mapping:['schedule' => 'schedule', 'scheduleAssignment' => 'id'])] ScheduleAssignment $scheduleAssignment,
        #[MapRequestPayload] ScheduleAssignmentPatchDTO $dto,
        EntitySerializerInterface $entitySerializer,
        ScheduleAssignmentRepository $scheduleAssignmentRepository,
    ): SuccessResponse
    {
        $scheduleAssignment = $entitySerializer->parseToEntity($dto, $scheduleAssignment);
        $scheduleAssignmentRepository->save($scheduleAssignment, true);
        $responseData = $entitySerializer->normalize($scheduleAssignment, ScheduleAssignmentNormalizerGroup::PRIVATE->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[OA\Delete(    
        summary: 'Remove schedule assignment',
        description: 'Removes the specified schedule assignment.
        </br>**Important:** This action can only be performed by the organization administrator.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Schedule assignment removed successfully'])]
    #[NotFoundResponseDoc('ScheduleAssignment not found')]
    #[Route('schedules/{schedule}/assignments/{scheduleAssignment}', name: 'schedule_assignment_remove', methods: ['DELETE'], requirements: ['schedule' => '\d+', 'scheduleAssignment' => '\d+'])]
    public function remove(
        #[MapEntity(mapping:['schedule' => 'schedule', 'scheduleAssignment' => 'id'])] ScheduleAssignment $scheduleAssignment,
        ScheduleAssignmentRepository $scheduleAssignmentRepository,
    ): SuccessResponse
    {
        $scheduleAssignmentRepository->remove($scheduleAssignment, true);
        
        return new SuccessResponse(['message' => 'Schedule assignment removed successfully']);
    }
}