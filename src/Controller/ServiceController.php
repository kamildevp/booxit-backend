<?php

declare(strict_types=1);

namespace App\Controller;

use App\Documentation\Response\ForbiddenResponseDoc;
use App\Documentation\Response\NotFoundResponseDoc;
use App\Documentation\Response\PaginatorResponseDoc;
use App\Documentation\Response\ServerErrorResponseDoc;
use App\Documentation\Response\SuccessResponseDoc;
use App\Documentation\Response\UnauthorizedResponseDoc;
use App\Documentation\Response\ValidationErrorResponseDoc;
use App\DTO\Service\ServiceCreateDTO;
use App\DTO\Service\ServiceListQueryDTO;
use App\DTO\Service\ServicePatchDTO;
use App\Entity\Service;
use App\Enum\Service\ServiceNormalizerGroup;
use App\Repository\ServiceRepository;
use App\Response\ResourceCreatedResponse;
use App\Response\SuccessResponse;
use App\Service\Auth\AccessRule\ServiceManagementPrivilegesRule;
use App\Service\Auth\Attribute\RestrictedAccess;
use App\Service\EntitySerializer\EntitySerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[ServerErrorResponseDoc]
#[OA\Tag('Service')]
class ServiceController extends AbstractController
{
    #[OA\Post(
        summary: 'Create a new service',
        description: 'Creates a new service for specified organization.
        </br></br>**Important:** This action can only be performed by organization admin.'
    )]
    #[SuccessResponseDoc(
        statusCode: 201,
        description: 'Created Service',
        dataModel: Service::class,
        dataModelGroups: ServiceNormalizerGroup::PRIVATE
    )]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ServiceManagementPrivilegesRule::class)]
    #[Route('service', name: 'service_new', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] ServiceCreateDTO $dto,
        EntitySerializerInterface $entitySerializer,
        ServiceRepository $serviceRepository,   
    ): ResourceCreatedResponse
    {
        $service = $entitySerializer->parseToEntity($dto, Service::class);
        $serviceRepository->save($service, true);
        $responseData = $entitySerializer->normalize($service, ServiceNormalizerGroup::PRIVATE->normalizationGroups());
        
        return new ResourceCreatedResponse($responseData);
    }

    #[OA\Get(
        summary: 'Get service',
        description: 'Returns the public data of the specified service.'
    )]
    #[SuccessResponseDoc(
        description: 'Requested Service Data',
        dataModel: Service::class,
        dataModelGroups: ServiceNormalizerGroup::PUBLIC
    )]
    #[NotFoundResponseDoc('Service not found')]
    #[Route('service/{service}', name: 'service_get', methods: ['GET'], requirements: ['service' => '\d+'])]
    public function get(Service $service, EntitySerializerInterface $entitySerializer): SuccessResponse
    {
        $responseData = $entitySerializer->normalize($service, ServiceNormalizerGroup::PUBLIC->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[OA\Patch(
        summary: 'Update service',
        description: 'Updates service data.
        </br>**Important:** This action can only be performed by organization admin.'
    )]
    #[SuccessResponseDoc(
        description: 'Updated Service Data',
        dataModel: Service::class,
        dataModelGroups: ServiceNormalizerGroup::PRIVATE
    )]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ServiceManagementPrivilegesRule::class)]
    #[Route('service/{service}', name: 'service_patch', methods: ['PATCH'], requirements: ['service' => '\d+'])]
    public function patch(
        Service $service, 
        EntitySerializerInterface $entitySerializer, 
        ServiceRepository $serviceRepository,
        #[MapRequestPayload] ServicePatchDTO $dto,
    ): SuccessResponse
    {
        $service = $entitySerializer->parseToEntity($dto, $service);
        $serviceRepository->save($service, true);
        $responseData = $entitySerializer->normalize($service, ServiceNormalizerGroup::PRIVATE->normalizationGroups());
        
        return new SuccessResponse($responseData);
    }

    #[OA\Delete(
        summary: 'Delete service',
        description: 'Deletes the specified service.
        </br>**Important:** This action can only be performed by organization admin.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Service removed successfully'])]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ServiceManagementPrivilegesRule::class)]
    #[Route('service/{service}', name: 'service_delete', methods: ['DELETE'], requirements: ['service' => '\d+'])]
    public function delete(        
        Service $service, 
        ServiceRepository $serviceRepository
    ): SuccessResponse
    {
        $serviceRepository->remove($service, true);
        
        return new SuccessResponse(['message' => 'Service removed successfully']);
    }

    #[OA\Get(
        summary: 'List services',
        description: 'Retrieves a paginated list of existing services with their public information.'
    )]
    #[PaginatorResponseDoc(
        description: 'Paginated users list', 
        dataModel: Service::class,
        dataModelGroups: ServiceNormalizerGroup::PUBLIC
    )]
    #[ValidationErrorResponseDoc]
    #[Route('service', name: 'service_list', methods: ['GET'])]
    public function list(
        ServiceRepository $serviceRepository, 
        EntitySerializerInterface $entitySerializer, 
        #[MapQueryString] ServiceListQueryDTO $queryDTO = new ServiceListQueryDTO,
    ): SuccessResponse
    {
        $paginationResult = $serviceRepository->paginate($queryDTO);
        $result = $entitySerializer->normalizePaginationResult($paginationResult, ServiceNormalizerGroup::PUBLIC->normalizationGroups());

        return new SuccessResponse($result);
    }
}