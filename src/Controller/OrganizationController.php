<?php

namespace App\Controller;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Exceptions\InvalidRequestException;
use App\Response\BadRequestResponse;
use App\Response\ForbiddenResponse;
use App\Response\NotFoundResponse;
use App\Response\ResourceCreatedResponse;
use App\Response\ServerErrorResponse;
use App\Response\SuccessResponse;
use App\Response\UnauthorizedResponse;
use App\Response\ValidationErrorResponse;
use App\Service\GetterHelper\GetterHelperInterface;
use App\Service\SetterHelper\SetterHelperInterface;
use Doctrine\ORM\EntityManagerInterface;
use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrganizationController extends AbstractController
{
    #[Route('organization', name: 'organization_new', methods: ['POST'])]
    public function new(
        SetterHelperInterface $setterHelper,
        ValidatorInterface $validator, 
        EntityManagerInterface $entityManager, 
        Request $request
        ): JsonResponse
    {
        $user = $this->getUser();

        if(!($user instanceof User)){
            return new UnauthorizedResponse;
        }

        $organization = new Organization();

        try{
            $setterHelper->updateObjectSettings($organization, $request->request->all(), ['Default']);
            $validationErrors = $setterHelper->getValidationErrors();
            
            $violations = $validator->validate($organization, groups: $setterHelper->getValidationGroups());

            foreach ($violations as $violation) {
                $requestParameterName = $setterHelper->getPropertyRequestParameter($violation->getPropertyPath());
                $validationErrors[$requestParameterName] = $violation->getMessage();
            }

            if(count($validationErrors) > 0){
                return new ValidationErrorResponse($validationErrors);
            }

            $setterHelper->runPostValidationTasks();

        }
        catch(InvalidRequestException){
            return new BadRequestResponse($setterHelper->getRequestErrors());
        }

        $entityManager->persist($organization);
        $entityManager->flush();
        
        $organizationMember = new OrganizationMember();
        $organizationMember->setAppUser($user);
        $organizationMember->setOrganization($organization);
        $organizationMember->setRoles(['MEMBER', 'ADMIN']);
        $entityManager->persist($organizationMember);
        $entityManager->flush();

        return new ResourceCreatedResponse($organization);
    }

    #[Route('organization/{organizationId}', name: 'organization_get', methods: ['GET'])]
    public function get(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, Request $request, int $organizationId): JsonResponse
    {
        $allowedDetails = ['members', 'services', 'schedules', 'admins'];
        $details = $request->query->get('details');
        $detailGroups = !is_null($details) ? explode(',', $details) : [];
        if(!empty(array_diff($detailGroups, $allowedDetails))){
            return new BadRequestResponse(['details' => 'Requested details are invalid']);
        }

        $range = $request->query->get('range');
        $detailGroups = array_map(fn($group) => 'organization-' . $group, $detailGroups);
        $groups = array_merge(['organization'], $detailGroups);

        $organization = $entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            return new NotFoundResponse;
        }
        
        try{
            $responseData = $getterHelper->get($organization, $groups, $range);
        }
        catch(InvalidRequestException){
            return new BadRequestResponse($getterHelper->getRequestErrors());
        }

        return new SuccessResponse($responseData);
    }

    #[Route('organization/{organizationId}', name: 'organization_modify', methods: ['PATCH'])]
    public function modify(
        ValidatorInterface $validator, 
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper, 
        Request $request, 
        int $organizationId
        ): JsonResponse
    {
        $organization = $entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            return new NotFoundResponse;
        }

        $currentUser = $this->getUser();
        if(!$currentUser){
            return new UnauthorizedResponse;
        }

        if(!($organization->hasMember($currentUser) && $organization->getMember($currentUser)->hasRoles(['ADMIN']))){
            return new ForbiddenResponse;
        }
        
        try{
            $setterHelper->updateObjectSettings($organization, $request->request->all(), [], ['Default']);
            $validationErrors = $setterHelper->getValidationErrors();
            
            $violations = $validator->validate($organization, groups: $setterHelper->getValidationGroups());

            foreach ($violations as $violation) {
                $requestParameterName = $setterHelper->getPropertyRequestParameter($violation->getPropertyPath());
                $validationErrors[$requestParameterName] = $violation->getMessage();
            }            

            if(count($validationErrors) > 0){
                return new ValidationErrorResponse($validationErrors);
            }

            $setterHelper->runPostValidationTasks();
        }
        catch(InvalidRequestException $e){
            return new BadRequestResponse($setterHelper->getRequestErrors());
        }

        $entityManager->flush();

        return new SuccessResponse($organization);
    }

    #[Route('organization/{organizationId}', name: 'organization_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $organizationId): JsonResponse
    {
        $organization = $entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            return new NotFoundResponse;
        }

        $currentUser = $this->getUser();
        if(!$currentUser){
            return new UnauthorizedResponse;
        }

        if(!($organization->hasMember($currentUser) && $organization->getMember($currentUser)->hasRoles(['ADMIN']))){
            return new ForbiddenResponse;
        }
        
        $entityManager->remove($organization);
        $entityManager->flush();

        return new SuccessResponse(['message' => 'Organization removed successfully']);
    }

    #[Route('organization/{organizationId}/members', name: 'organization_modifyMembers', methods: ['POST', 'PATCH', 'PUT', 'DELETE'])]
    public function modifyMembers(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper,
        Request $request, 
        int $organizationId
        ): JsonResponse
    {
        $organization = $entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            return new NotFoundResponse;
        }

        $currentUser = $this->getUser();
        if(!$currentUser){
            return new UnauthorizedResponse;
        }

        if(!($organization->hasMember($currentUser) && $organization->getMember($currentUser)->hasRoles(['ADMIN']))){
            return new ForbiddenResponse;
        }

        try{
            $modficationTypeMap = ['POST' => 'ADD', 'PATCH' => 'PATCH', 'PUT' => 'OVERWRITE', 'DELETE' => 'REMOVE'];

            $parameters = $request->request->all();
            $parameters['modificationType'] = $modficationTypeMap[$request->getMethod()];
            $setterHelper->updateObjectSettings($organization, $parameters, ['members'], []);
            $validationErrors = $setterHelper->getValidationErrors();

            if(count($validationErrors) > 0){
                return new ValidationErrorResponse($validationErrors);
            }

            $setterHelper->runPostValidationTasks();
        }
        catch(InvalidRequestException $e){
            return new BadRequestResponse($setterHelper->getRequestErrors());
        }

        $entityManager->flush();

        $actionType = ['POST' => 'added', 'PATCH' => 'modified', 'PUT' => 'overwritten', 'DELETE' => 'removed'];

        return new SuccessResponse(['message' => "Members {$actionType[$request->getMethod()]} successfully"]);
    }

    #[Route('organization/{organizationId}/services', name: 'organization_modifyServices', methods: ['POST', 'PATCH', 'PUT', 'DELETE'])]
    public function modifyServices(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper,
        Request $request, 
        int $organizationId
        ): JsonResponse
    {
        $organization = $entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            return new NotFoundResponse;
        }

        $currentUser = $this->getUser();
        if(!$currentUser){
            return new UnauthorizedResponse;
        }

        if(!($organization->hasMember($currentUser) && $organization->getMember($currentUser)->hasRoles(['ADMIN']))){
            return new ForbiddenResponse;
        }

        try{
            $modficationTypeMap = ['POST' => 'ADD', 'PATCH' => 'PATCH', 'PUT' => 'OVERWRITE', 'DELETE' => 'REMOVE'];

            $parameters = $request->request->all();
            $parameters['modificationType'] = $modficationTypeMap[$request->getMethod()];
            $setterHelper->updateObjectSettings($organization, $parameters, ['services'], []);

            $validationErrors = $setterHelper->getValidationErrors();

            if(count($validationErrors) > 0){
                return new ValidationErrorResponse($validationErrors);
            }

            $setterHelper->runPostValidationTasks();
        }
        catch(InvalidRequestException $e){
            return new BadRequestResponse($setterHelper->getRequestErrors());
        }
    
        $entityManager->flush();

        $actionType = ['POST' => 'added', 'PATCH' => 'modified', 'PUT' => 'overwritten', 'DELETE' => 'removed'];

        return new SuccessResponse(['message' => "Services {$actionType[$request->getMethod()]} successfully"]);
    }

    #[Route('organization', name: 'organizations_get', methods: ['GET'])]
    public function getOrganizations(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, Request $request): JsonResponse
    {
        $filter = $request->query->get('filter');
        $range = $request->query->get('range');

        $organizations = $entityManager->getRepository(Organization::class)->findByPartialName($filter ?? '');
        
        try{
            $responseData = $getterHelper->getCollection($organizations, ['organizations'], $range);
        }
        catch(InvalidRequestException $e){
            return new BadRequestResponse($getterHelper->getRequestErrors());
        }

        return new SuccessResponse($responseData);
    }

    #[Route('organization/{organizationId}/members', name: 'organization_getMembers', methods: ['GET'])]
    public function getMembers(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, Request $request, int $organizationId): JsonResponse
    {
        $filter = $request->query->get('filter');
        $range = $request->query->get('range');

        $organization = $entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            return new NotFoundResponse;
        }
        
        if(is_null($filter)){
            $members = $organization->getMembers();
        }
        else{
            $members = $organization->getMembers()->filter(function($element) use ($filter){
                $user = $element->getAppUser();
                return str_contains(strtolower($user->getName()), strtolower($filter)) || str_contains(strtolower($user->getEmail()), strtolower($filter));
            });
        }

        try{
            $responseData = $getterHelper->getCollection($members, ['organization-members'], $range);
        }
        catch(InvalidRequestException $e){
            return new BadRequestResponse($getterHelper->getRequestErrors());
        }

        return new SuccessResponse($responseData);
    }

    #[Route('organization/{organizationId}/services', name: 'organization_getServices', methods: ['GET'])]
    public function getServices(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, Request $request, int $organizationId): JsonResponse
    {
        $filter = $request->query->get('filter');
        $range = $request->query->get('range');

        $organization = $entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            return new NotFoundResponse;
        }
        
        if(is_null($filter)){
            $services = $organization->getServices();
        }
        else{
            $services = $organization->getServices()->filter(function($element) use ($filter){
                return str_contains(strtolower($element->getName()), strtolower($filter));
            });
        }

        try{
            $responseData = $getterHelper->getCollection($services, ['organization-services'], $range);
        }
        catch(InvalidRequestException){
            return new BadRequestResponse($getterHelper->getRequestErrors());
        }

        return new SuccessResponse($responseData);
    }

    #[Route('organization/{organizationId}/schedules', name: 'organization_getSchedules', methods: ['GET'])]
    public function getSchedules(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, Request $request, int $organizationId): JsonResponse
    {
        $filter = $request->query->get('filter');
        $range = $request->query->get('range');

        $organization = $entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            return new NotFoundResponse;
        }
        
        if(is_null($filter)){
            $schedules = $organization->getSchedules();
        }
        else{
            $schedules = $organization->getSchedules()->filter(function($element) use ($filter){
                return str_contains(strtolower($element->getName()), strtolower($filter));
            });
        }

        try{
            $responseData = $getterHelper->getCollection($schedules, ['organization-schedules'], $range);
        }
        catch(InvalidRequestException $e){
            return new BadRequestResponse($getterHelper->getRequestErrors());
        }

        return new SuccessResponse($responseData);
    }

    #[Route('organization/{organizationId}/banner', name: 'organization_addBanner', methods: ['POST'])]
    public function addBanner(EntityManagerInterface $entityManager, Request $request, int $organizationId): JsonResponse
    {

        $organization = $entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            return new NotFoundResponse;
        }

        $currentUser = $this->getUser();
        if(!$currentUser){
            return new UnauthorizedResponse;
        }

        if(!($organization->hasMember($currentUser) && $organization->getMember($currentUser)->hasRoles(['ADMIN']))){
            return new ForbiddenResponse;
        }

        $bannerFile = $request->files->get('banner');
        if(!$bannerFile){
            return new BadRequestResponse(['banner' => 'File not found']);
        }

        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];
        $mimeType = $bannerFile->getClientMimeType();
        if(!in_array($mimeType, $allowedTypes)){
            return new ValidationErrorResponse(['banner' => 'Invalid file type']);
        }

        $maxSize = 10000000;
        $bannerSize = $bannerFile->getSize();
        if($bannerSize > $maxSize){
            return new ValidationErrorResponse(['banner' => 'Maximum file size is 10MB']);
        }

        $fileName = uniqid() . '.' . $bannerFile->guessExtension();
        $storagePath = $this->getParameter('storage_directory') . $this->getParameter('organization_banner_directory');

        try{
            $bannerFile->move(
                $storagePath,
                $fileName
            );

            $banner = $organization->getBanner();
            if(!is_null($banner)){
                (new Filesystem)->remove($this->getParameter('storage_directory') . $banner);
            }
        }
        catch(FileException){
            return new ServerErrorResponse('File system error');
        }

        $organization->setBanner($this->getParameter('organization_banner_directory') . '/' . $fileName);
        $entityManager->flush();

        return new SuccessResponse(['message' => 'Banner uploaded successfully']);
    }

    #[Route('organization/{organizationId}/banner', name: 'organization_getBanner', methods: ['GET'])]
    public function getBanner(EntityManagerInterface $entityManager, int $organizationId): Response
    {

        $organization = $entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            return new NotFoundResponse;
        }

        $banner = $organization->getBanner();
        if(!is_null($banner)){
            try{
                return new BinaryFileResponse($this->getParameter('storage_directory') . $banner);
            }
            catch (FileException){
                return new ServerErrorResponse('File system error');
            }
        }
        else{
            return new Response();
        }
    }
}
