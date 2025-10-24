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
use App\DTO\OrganizationMember\OrganizationMemberCreateDTO;
use App\DTO\OrganizationMember\OrganizationMemberListQueryDTO;
use App\DTO\OrganizationMember\OrganizationMemberPatchDTO;
use App\DTO\OrganizationMember\OrganizationMemberScheduleAssignmentListQueryDTO;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\Schedule;
use App\Entity\ScheduleAssignment;
use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use App\Enum\OrganizationMember\OrganizationMemberNormalizerGroup;
use App\Enum\ScheduleAssignment\ScheduleAssignmentNormalizerGroup;
use App\Repository\OrganizationMemberRepository;
use App\Repository\ScheduleAssignmentRepository;
use App\Response\SuccessResponse;
use App\Service\Auth\AccessRule\OrganizationManagementPrivilegesRule;
use App\Service\Auth\AccessRule\OrganizationMemberScheduleAssignmentsViewPrivilegesRule;
use App\Service\Auth\Attribute\RestrictedAccess;
use App\Service\Entity\OrganizationMemberService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[ServerErrorResponseDoc]
#[OA\Tag('OrganizationMember')]
class OrganizationMemberController extends AbstractController
{
    #[OA\Post(
        summary: 'Add new organization member',
        description: 'Adds new organization member with specified role.
        </br>**Important:** This action can only be performed by the organization administrator'
    )]
    #[SuccessResponseDoc(
        statusCode: 200,
        description: 'Added OrganizationMember',
        dataModel: OrganizationMember::class,
        dataModelGroups: OrganizationMemberNormalizerGroup::PRIVATE
    )]
    #[NotFoundResponseDoc('Organization not found')]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(OrganizationManagementPrivilegesRule::class)]
    #[Route('organizations/{organization}/members', name: 'organization_member_create', methods: ['POST'], requirements: ['organization' => '\d+'])]
    public function create(
        Organization $organization,
        #[MapRequestPayload] OrganizationMemberCreateDTO $dto,
        EntitySerializerInterface $entitySerializer,
        OrganizationMemberService $organizationMemberService,
    ): SuccessResponse
    {
        $organizationMember = $organizationMemberService->createOrganizationMember($organization, $dto->userId, OrganizationRole::from($dto->role));
        $responseData = $entitySerializer->normalize($organizationMember, OrganizationMemberNormalizerGroup::PRIVATE->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[OA\Get(
        summary: 'List organization members',
        description: 'Retrieves a paginated list of organization members with their public information.'
    )]
    #[PaginatorResponseDoc(
        description: 'Paginated organization members list', 
        dataModel: OrganizationMember::class,
        dataModelGroups: OrganizationMemberNormalizerGroup::PUBLIC
    )]
    #[NotFoundResponseDoc('Organization not found')]
    #[ValidationErrorResponseDoc]
    #[Route('organizations/{organization}/members', name: 'organization_member_list', methods: ['GET'], requirements: ['organization' => '\d+'])]
    public function list(
        Organization $organization,
        EntitySerializerInterface $entitySerializer, 
        OrganizationMemberRepository $organizationMemberRepository, 
        #[MapQueryString] OrganizationMemberListQueryDTO $queryDTO = new OrganizationMemberListQueryDTO,
    ): SuccessResponse
    {
        $paginationResult = $organizationMemberRepository->paginateRelatedTo(
            $queryDTO, 
            ['organization' => $organization], 
            ['appUser' => User::class]
        );
        $result = $entitySerializer->normalizePaginationResult($paginationResult, OrganizationMemberNormalizerGroup::PUBLIC->normalizationGroups());

        return new SuccessResponse($result);
    }

    #[OA\Get(
        summary: 'Get organization member',
        description: 'Returns the public data of the specified organization member.'
    )]
    #[SuccessResponseDoc(
        description: 'Requested OrganizationMember Data',
        dataModel: OrganizationMember::class,
        dataModelGroups: OrganizationMemberNormalizerGroup::PUBLIC
    )]
    #[NotFoundResponseDoc('OrganizationMember not found')]
    #[Route('organizations/{organization}/members/{organizationMember}', name: 'organization_member_get', methods: ['GET'], requirements: ['organization' => '\d+', 'organizationMember' => '\d+'])]
    public function get(
        #[MapEntity(mapping:['organization' => 'organization', 'organizationMember' => 'id'])] OrganizationMember $organizationMember, 
        EntitySerializerInterface $entitySerializer
    ): SuccessResponse
    {
        $responseData = $entitySerializer->normalize($organizationMember, OrganizationMemberNormalizerGroup::PUBLIC->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[OA\Patch(
        summary: 'Update organization member',
        description: 'Updates organization member data.
        </br>**Important:** This action can only be performed by the organization administrator.'
    )]
    #[SuccessResponseDoc(
        description: 'Updated OrganizationMember Data',
        dataModel: OrganizationMember::class,
        dataModelGroups: OrganizationMemberNormalizerGroup::PUBLIC
    )]
    #[ConflictResponseDoc('The admin role cannot be removed because this user is the only administrator of the organization.')]
    #[NotFoundResponseDoc('OrganizationMember not found')]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(OrganizationManagementPrivilegesRule::class)]
    #[Route('organizations/{organization}/members/{organizationMember}', name: 'organization_member_patch', methods: ['PATCH'], requirements: ['organization' => '\d+', 'organizationMember' => '\d+'])]
    public function patch(
        #[MapEntity(mapping:['organization' => 'organization', 'organizationMember' => 'id'])] OrganizationMember $organizationMember,
        #[MapRequestPayload] OrganizationMemberPatchDTO $dto,
        EntitySerializerInterface $entitySerializer,
        OrganizationMemberService $organizationMemberService,
    ): SuccessResponse
    {
        $organizationMember = $organizationMemberService->patchOrganizationMember($organizationMember, $dto);
        $responseData = $entitySerializer->normalize($organizationMember, OrganizationMemberNormalizerGroup::PRIVATE->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[OA\Delete(    
        summary: 'Remove organization member',
        description: 'Removes the specified organization member.
        </br>**Important:** This action can only be performed by the organization administrator.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Organization member removed successfully'])]
    #[ConflictResponseDoc('Cannot remove the only administrator of organization.')]
    #[NotFoundResponseDoc('OrganizationMember not found')]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(OrganizationManagementPrivilegesRule::class)]
    #[Route('organizations/{organization}/members/{organizationMember}', name: 'organization_member_remove', methods: ['DELETE'], requirements: ['organization' => '\d+', 'organizationMember' => '\d+'])]
    public function remove(
        OrganizationMemberService $organizationMemberService, 
        #[MapEntity(mapping:['organization' => 'organization', 'organizationMember' => 'id'])] OrganizationMember $organizationMember
    ): SuccessResponse
    {
        $organizationMemberService->removeOrganizationMember($organizationMember);
        
        return new SuccessResponse(['message' => 'Organization member removed successfully']);
    }

    #[OA\Get(
        summary: 'List organization member schedule assignments',
        description: 'Retrieves a paginated list of organization member\'s schedule assignments'
    )]
    #[PaginatorResponseDoc(
        description: 'Paginated schedule assignments list', 
        dataModel: ScheduleAssignment::class,
        dataModelGroups: ScheduleAssignmentNormalizerGroup::ORGANIZATION_MEMBER_SCHEDULE_ASSIGNMENTS
    )]
    #[NotFoundResponseDoc('OrganizationMember not found')]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(OrganizationMemberScheduleAssignmentsViewPrivilegesRule::class)]
    #[Route('organizations/{organization}/members/{organizationMember}/schedule-assignments', name: 'organization_member_schedule_assignment_list', methods: ['GET'], requirements: ['organization' => '\d+', 'organizationMember' => '\d+'])]
    public function getMemberScheduleAssignments(
        #[MapEntity(mapping:['organization' => 'organization', 'organizationMember' => 'id'])] OrganizationMember $organizationMember, 
        EntitySerializerInterface $entitySerializer,
        ScheduleAssignmentRepository $scheduleAssignmentRepository,
        #[MapQueryString] OrganizationMemberScheduleAssignmentListQueryDTO $queryDTO = new OrganizationMemberScheduleAssignmentListQueryDTO,
    ): SuccessResponse
    {
        $paginationResult = $scheduleAssignmentRepository->paginateRelatedTo(
            $queryDTO, 
            ['organizationMember' => $organizationMember], 
            ['schedule' => Schedule::class]
        );
        $result = $entitySerializer->normalizePaginationResult($paginationResult, ScheduleAssignmentNormalizerGroup::ORGANIZATION_MEMBER_SCHEDULE_ASSIGNMENTS->normalizationGroups());

        return new SuccessResponse($result);
    }
}