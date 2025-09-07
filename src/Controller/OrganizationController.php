<?php

declare(strict_types=1);

namespace App\Controller;

use App\Documentation\Request\MediaTypeRequestDoc;
use App\Documentation\Response\BinaryFileResponseDoc;
use App\Documentation\Response\ForbiddenResponseDoc;
use App\Documentation\Response\NotFoundResponseDoc;
use App\Documentation\Response\PaginatorResponseDoc;
use App\Documentation\Response\ServerErrorResponseDoc;
use App\Documentation\Response\SuccessResponseDoc;
use App\Documentation\Response\UnauthorizedResponseDoc;
use App\Documentation\Response\ValidationErrorResponseDoc;
use App\DTO\Organization\OrganizationCreateDTO;
use App\DTO\Organization\OrganizationListQueryDTO;
use App\DTO\Organization\OrganizationPatchDTO;
use App\Entity\Organization;
use App\Enum\File\UploadType;
use App\Enum\Organization\OrganizationNormalizerGroup;
use App\Repository\OrganizationRepository;
use App\Response\NotFoundResponse;
use App\Response\ResourceCreatedResponse;
use App\Response\SuccessResponse;
use App\Service\Auth\AccessRule\OrganizationAdminRule;
use App\Service\Auth\Attribute\RestrictedAccess;
use App\Service\Entity\FileService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[ServerErrorResponseDoc]
#[OA\Tag('Organization')]
class OrganizationController extends AbstractController
{
    #[OA\Post(
        summary: 'Create a new organization',
        description: 'Creates a new organization. The user who creates the organization automatically becomes its administrator.
        </br>**Important:** This action can only be performed by logged in user'
    )]
    #[SuccessResponseDoc(
        statusCode: 201,
        description: 'Created Organization',
        dataModel: Organization::class,
        dataModelGroups: OrganizationNormalizerGroup::PRIVATE
    )]
    #[ValidationErrorResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess]
    #[Route('organization', name: 'organization_new', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] OrganizationCreateDTO $dto,
        EntitySerializerInterface $entitySerializer,
        OrganizationRepository $organizationRepository,   
    ): ResourceCreatedResponse
    {
        $organization = $entitySerializer->parseToEntity($dto, Organization::class);
        $organizationRepository->save($organization, true);
        $responseData = $entitySerializer->normalize($organization, OrganizationNormalizerGroup::PRIVATE->normalizationGroups());
        
        return new ResourceCreatedResponse($responseData);
    }

    #[OA\Get(
        summary: 'Get organization',
        description: 'Returns the public data of the specified organization.'
    )]
    #[SuccessResponseDoc(
        description: 'Requested Organization Data',
        dataModel: Organization::class,
        dataModelGroups: OrganizationNormalizerGroup::PUBLIC
    )]
    #[NotFoundResponseDoc('Organization not found')]
    #[Route('organization/{organization}', name: 'organization_get', methods: ['GET'], requirements: ['organization' => '\d+'])]
    public function get(Organization $organization, EntitySerializerInterface $entitySerializer): SuccessResponse
    {
        $responseData = $entitySerializer->normalize($organization, OrganizationNormalizerGroup::PUBLIC->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[OA\Patch(
        summary: 'Update organization',
        description: 'Updates organization data.
        </br>**Important:** This action can only be performed by the organization administrator.'
    )]
    #[SuccessResponseDoc(
        description: 'Updated Organization Data',
        dataModel: Organization::class,
        dataModelGroups: OrganizationNormalizerGroup::PRIVATE
    )]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(OrganizationAdminRule::class)]
    #[Route('organization/{organization}', name: 'organization_patch', methods: ['PATCH'], requirements: ['organization' => '\d+'])]
    public function patch(
        Organization $organization, 
        EntitySerializerInterface $entitySerializer, 
        OrganizationRepository $organizationRepository,
        #[MapRequestPayload] OrganizationPatchDTO $dto,
    ): SuccessResponse
    {
        $organization = $entitySerializer->parseToEntity($dto, $organization);
        $organizationRepository->save($organization, true);
        $responseData = $entitySerializer->normalize($organization, OrganizationNormalizerGroup::PRIVATE->normalizationGroups());
        
        return new SuccessResponse($responseData);
    }

    #[OA\Delete(
        summary: 'Delete organization',
        description: 'Deletes the specified organization.
        </br>**Important:** This action can only be performed by the organization administrator. The organization is soft-deleted, meaning a new organization cannot be created with the same name.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Organization removed successfully'])]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(OrganizationAdminRule::class)]
    #[Route('organization/{organization}', name: 'organization_delete', methods: ['DELETE'], requirements: ['organization' => '\d+'])]
    public function delete(        
        Organization $organization, 
        OrganizationRepository $organizationRepository
    ): SuccessResponse
    {
        $organizationRepository->remove($organization, true);
        
        return new SuccessResponse(['message' => 'Organization removed successfully']);
    }

    #[OA\Get(
        summary: 'List organizations',
        description: 'Retrieves a paginated list of existing organizations with their public information.'
    )]
    #[PaginatorResponseDoc(
        description: 'Paginated users list', 
        dataModel: Organization::class,
        dataModelGroups: OrganizationNormalizerGroup::PUBLIC
    )]
    #[ValidationErrorResponseDoc]
    #[Route('organization', name: 'organization_list', methods: ['GET'])]
    public function list(
        OrganizationRepository $organizationRepository, 
        EntitySerializerInterface $entitySerializer, 
        #[MapQueryString] OrganizationListQueryDTO $queryDTO = new OrganizationListQueryDTO,
    ): SuccessResponse
    {
        $paginationResult = $organizationRepository->paginate($queryDTO);
        $result = $entitySerializer->normalizePaginationResult($paginationResult, OrganizationNormalizerGroup::PUBLIC->normalizationGroups());

        return new SuccessResponse($result);
    }

    #[OA\Put(
        summary: 'Update organization banner',
        description: 'Updates organization banner.
        </br>**Important:** This action can only be performed by the organization administrator.'
    )]
    #[MediaTypeRequestDoc(UploadType::ORGANIZATION_BANNER)]
    #[SuccessResponseDoc(dataExample: ['message' => 'Organization banner updated successfully'])]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[NotFoundResponseDoc('Organization not found')]
    #[RestrictedAccess(OrganizationAdminRule::class)]
    #[Route('organization/{organization}/banner', name: 'organization_banner_put', methods: ['PUT'], requirements: ['organization' => '\d+'])]
    public function updateBanner(
        Organization $organization,  
        OrganizationRepository $organizationRepository,
        FileService $fileService,
        Request $request,
    ): SuccessResponse
    {
        $content = $request->getContent();
        $contentType = $request->headers->get('Content-Type');

        $banner = $fileService->uploadRawFile($content, $contentType, UploadType::ORGANIZATION_BANNER, $organization->getBannerFile());
        $organization->setBannerFile($banner);
        $organizationRepository->save($organization, true);
        
        return new SuccessResponse(['message' => 'Organization banner updated successfully']);
    }

    #[OA\Get(
        summary: 'Get organization banner',
        description: 'Returns banner of the specified organization.'
    )]
    #[BinaryFileResponseDoc(UploadType::ORGANIZATION_BANNER)]
    #[NotFoundResponseDoc(messages: ['Organization not found', 'Organization banner not found'])]
    #[Route('organization/{organization}/banner', name: 'organization_banner_get', methods: ['GET'], requirements: ['organization' => '\d+'])]
    public function getBanner(Organization $organization): NotFoundResponse|BinaryFileResponse
    {
        $banner = $organization->getBannerFile();
        if(!$banner){
            return new NotFoundResponse('Organization banner not found');
        }

        $response = new BinaryFileResponse($banner->getPath());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);

        return $response;
    }
}