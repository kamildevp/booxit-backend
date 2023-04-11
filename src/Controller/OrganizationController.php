<?php

namespace App\Controller;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Exceptions\InvalidRequestException;
use App\Service\GetterHelper\GetterHelperInterface;
use App\Service\RequestValidator\RequestValidator;
use App\Service\SetterHelper\SetterHelperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrganizationController extends AbstractController
{
    #[Route('organization', name: 'organization_new', methods: ['POST'])]
    public function new(ValidatorInterface $validator, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if(!($user instanceof User)){
            return $this->json([
                'message' => 'Access Denied'
            ]);
        }

        try{
            (new RequestValidator)->validateRequest($request->request->all(), ['name']);
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'message' => 'Invalid Request',
                'error' => $e->getMessage()
            ]);
        }

        $organization = new Organization();
        $organization->setName($request->get('name'));

        $violations = $validator->validate($organization);
        
        if(count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            return $this->json([
                'message' => 'Validation Error',
                'errors' => $errors
            ]);
        }


        $entityManager->persist($organization);
        $entityManager->flush();
        
        $organizationMember = new OrganizationMember();
        $organizationMember->setAppUser($user);
        $organizationMember->setOrganization($organization);
        $organizationMember->setRoles(['MEMBER', 'ADMIN']);
        $entityManager->persist($organizationMember);
        $entityManager->flush();

        return $this->json([
            'message' => 'Organization created successfully'
        ]);
    }

    #[Route('organization/{id}', name: 'organization_get', methods: ['GET'])]
    public function get(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, int $id): JsonResponse
    {
        $organization = $entityManager->getRepository(Organization::class)->find($id);
        if(!($organization instanceof Organization)){
            return $this->json([
                'message' => 'Organization not found'
            ]);
        }
        
        $responseData = $getterHelper->get($organization);

        return $this->json($responseData);
    }

    #[Route('organization/{id}', name: 'organization_modify', methods: ['PATCH'])]
    public function modify(
        ValidatorInterface $validator, 
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper, 
        Request $request, 
        int $id
        ): JsonResponse
    {
        $organization = $entityManager->getRepository(Organization::class)->find($id);
        if(!($organization instanceof Organization)){
            return $this->json([
                'message' => 'Organization not found'
            ]);
        }

        $currentUser = $this->getUser();
        if(!($currentUser && $organization->hasMember($currentUser) && $organization->getMember($currentUser)->hasRoles(['ADMIN']))){
            return $this->json([
                'message' => 'Access Denied'
            ]);
        }
        
        try{
            $setterHelper->updateObjectSettings($organization, $request->request->all());
            
            $violations = $validator->validate($organization);
            
            if(count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()][] = $violation->getMessage();
                }
                return $this->json([
                    'message' => 'Validation Error',
                    'errors' => $errors
                ]);
            }

            $setterHelper->runPostValidationTasks();
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'message' => 'Modification Failed',
                'errors' => $e->getMessage()
            ]);
        }

        $entityManager->flush();

        return $this->json([
            'message' => 'Organization settings modified successfully'
        ]);
    }

    #[Route('organization/{id}', name: 'organization_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $organization = $entityManager->getRepository(Organization::class)->find($id);
        if(!($organization instanceof Organization)){
            return $this->json([
                'message' => 'Organization not found'
            ]);
        }

        $currentUser = $this->getUser();
        if(!($currentUser && $organization->hasMember($currentUser) && $organization->getMember($currentUser)->hasRoles(['ADMIN']))){
            return $this->json([
                'message' => 'Access Denied'
            ]);
        }
        
        $entityManager->remove($organization);
        $entityManager->flush();

        return $this->json([
            'message' => 'Organization removed successfully'
        ]);
    }

    #[Route('organization/{organizationId}/member', name: 'organization_newMember', methods: ['POST'])]
    public function newMember(EntityManagerInterface $entityManager, Request $request, int $organizationId): JsonResponse
    {
        $organization = $entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            return $this->json([
                'message' => 'Organization not found'
            ]);
        }

        $currentUser = $this->getUser();

        if(!($currentUser && $organization->hasMember($currentUser) && $organization->getMember($currentUser)->hasRoles(['ADMIN']))){
            return $this->json([
                'message' => 'Access Denied'
            ]);
        }

        try{
            (new RequestValidator)->validateRequest($request->request->all(), ['userId']);
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'message' => 'Invalid Request',
                'error' => $e->getMessage()
            ]);
        }

        $user = $entityManager->getRepository(User::class)->find($request->get('userId'));
        if(!$user){
            return $this->json([
                'message' => 'Invalid Request',
                'error' => 'User not found'
            ]);
        }

        if($organization->hasMember($user))
        {
            return $this->json([
                'message' => 'Invalid Request',
                'error' => 'Member already exists'
            ]);
        }

        $member = new OrganizationMember();
        $member->setAppUser($user);
        $member->setOrganization($organization);
        $member->setRoles(['MEMBER']);
        $entityManager->persist($member);
        $entityManager->flush();

        return $this->json([
            'message' => 'Member added successfully'
        ]);
    }

    
    #[Route('organization/{organizationId}/members', name: 'organization_getMembers', methods: ['GET'])]
    public function getMembers(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, int $organizationId): JsonResponse
    {
        $organization = $entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            return $this->json([
                'message' => 'Organization not found'
            ]);
        }

        $members = $organization->getMembers();
        
        $membersInfo = [];
        foreach($members as $member){
            $membersInfo[] = array_merge($getterHelper->get($member->getAppUser()), ['memberId' => $member->getId(), 'roles' => $member->getRoles()]);
        }

        return $this->json([
            'members' => $membersInfo
        ]);
    }

    #[Route('organization/{organizationId}/member/{memberId}', name: 'organization_getMember', methods: ['GET'])]
    public function getMember(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, int $organizationId,int $memberId): JsonResponse
    {
        $organization = $entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            return $this->json([
                'message' => 'Organization not found'
            ]);
        }

        $members = $organization->getMembers();

        $member = $members->findFirst(function($key, $member) use ($memberId){
            return $member->getId() === $memberId;
        });

        if(!$member){
            return $this->json([
                'message' => 'Member not found'
            ]);
        }

        $memberInfo = array_merge($getterHelper->get($member->getAppUser()), ['memberId' => $member->getId(), 'roles' => $member->getRoles()]);

        return $this->json([
            $memberInfo
        ]);
    }

    #[Route('organization/{organizationId}/member/{memberId}', name: 'organization_modifyMember', methods: ['PATCH'])]
    public function modifyMember(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper, 
        ValidatorInterface $validator,
        Request $request, 
        int $organizationId, 
        int $memberId
        ): JsonResponse
    {
        $organization = $entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            return $this->json([
                'message' => 'Organization not found'
            ]);
        }

        $currentUser = $this->getUser();

        if(!($currentUser && $organization->hasMember($currentUser) && $organization->getMember($currentUser)->hasRoles(['ADMIN']))){
            return $this->json([
                'message' => 'Access Denied'
            ]);
        }

        $members = $organization->getMembers();

        $member = $members->findFirst(function($key, $member) use ($memberId){
            return $member->getId() === $memberId;
        });

        if(!$member){
            return $this->json([
                'message' => 'Member not found'
            ]);
        }

        try{
            $setterHelper->updateObjectSettings($member, $request->request->all());
            $violations = $validator->validate($member);
            
            if(count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()][] = $violation->getMessage();
                }
                return $this->json([
                    'message' => 'Validation Error',
                    'errors' => $errors
                ]);
            }

            $setterHelper->runPostValidationTasks();
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'message' => 'Modification Failed',
                'errors' => $e->getMessage()
            ]);
        }

        $entityManager->flush();
        return $this->json([
            'message' => 'Member settings changed successfully'
        ]);
    }

    #[Route('organization/{organizationId}/member/{memberId}', name: 'organization_deleteMember', methods: ['DELETE'])]
    public function deleteMember(EntityManagerInterface $entityManager, int $organizationId, int $memberId): JsonResponse
    {
        $organization = $entityManager->getRepository(Organization::class)->find($organizationId);
        if(!($organization instanceof Organization)){
            return $this->json([
                'message' => 'Organization not found'
            ]);
        }

        $currentUser = $this->getUser();

        if(!($currentUser && $organization->hasMember($currentUser) && $organization->getMember($currentUser)->hasRoles(['ADMIN']))){
            return $this->json([
                'message' => 'Access Denied'
            ]);
        }

        $members = $organization->getMembers();

        $member = $members->findFirst(function($key, $member) use ($memberId){
            return $member->getId() === $memberId;
        });

        if(!$member){
            return $this->json([
                'message' => 'Member not found'
            ]);
        }

        if($member->hasRoles(['ADMIN'])){
            return $this->json([
                'message' => 'Cannot remove member with ADMIN role'
            ]);
        }

        $entityManager->remove($member);
        $entityManager->flush();

        return $this->json([
            'message' => 'Member removed successfully'
        ]);
    }


}
