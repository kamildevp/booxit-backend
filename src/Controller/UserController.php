<?php

namespace App\Controller;

use App\Entity\EmailConfirmation;
use App\Entity\User;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\MailingHelperException;
use App\Service\GetterHelper\GetterHelperInterface;
use App\Service\MailingHelper\MailingHelper;
use App\Service\RequestValidator\RequestValidator;
use App\Service\SetterHelper\SetterHelperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class UserController extends AbstractController
{

    #[Route('user', name: 'user_new', methods: ['POST'])]
    public function new(
        ValidatorInterface $validator, 
        SetterHelperInterface $setterHelper, 
        EntityManagerInterface $entityManager, 
        // MailingHelper $mailingHelper, //uncomment when mailing provider is available
        Request $request
        ): JsonResponse
    {
        $user = new User();

        try{
            $setterHelper->updateObjectSettings($user, $request->request->all(), ['Default']);
            $validationErrors = $setterHelper->getValidationErrors();

            $violations = $validator->validate($user, groups: $setterHelper->getValidationGroups());
            $validationErrors = $setterHelper->getValidationErrors();

            foreach ($violations as $violation) {
                $requestParameterName = $setterHelper->getPropertyRequestParameter($violation->getPropertyPath());
                $validationErrors[$requestParameterName] = $violation->getMessage();
            }

            if(count($validationErrors) > 0){
                return $this->json([
                    'status' => 'Failure',
                    'message' => 'Validation Error',
                    'errors' => $validationErrors
                ]);
            }

            $setterHelper->runPostValidationTasks();

        }
        catch(InvalidRequestException $e){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }

        // $user->setVerified(false);   //uncomment when mailing provider is available
        $user->setVerified(true); //remove when mailing provider is available

        $entityManager->persist($user);
        $entityManager->flush();

        try{
            // $mailingHelper->newEmailVerification($user, $user->getEmail()); //uncomment when mailing provider is available
        }
        catch(MailingHelperException){
            $entityManager->remove($user);
            $entityManager->flush();
            return $this->json([
                'status' => 'Failure',
                'message' => 'Server Error'
            ]);
        }

        return $this->json([
            'status' => 'Success',
            'message' => 'Account created successfully'
        ]);
    }

    #[Route('user_verify', name: 'user_verify', methods: ['GET'])]
    public function verify(EntityManagerInterface $entityManager, VerifyEmailHelperInterface $verifyEmailHelper, Request $request)
    {
        $id = (int)$request->get('id');
        $emailConfirmation = $entityManager->getRepository(EmailConfirmation::class)->find($id);
        if(!$emailConfirmation){
            return $this->render(
                'emailVerification.html.twig', 
                ['header' => 'Verification Failed', 'description' => 'Verification link is invalid']
            );
        }

        try{
            $verifyEmailHelper->validateEmailConfirmation($request->getUri(), $emailConfirmation->getId(), $emailConfirmation->getEmail());
            $user = $emailConfirmation->getCreator();
            $user->setEmail($emailConfirmation->getEmail());
            $user->setVerified(true);
            $entityManager->remove($emailConfirmation);
            $entityManager->flush();
            return $this->render(
                'emailVerification.html.twig', 
                ['header' => 'Verification Completed', 'description' => 'Your email was verfied successfully']
            );

        } 
        catch(VerifyEmailExceptionInterface $e) {
            return $this->render(
                'emailVerification.html.twig', 
                ['header' => 'Verification Failed', 'description' => $e->getReason()]
            );
        }
        
    }

    #[Route('user/{id}', name: 'user_get', methods: ['GET'])]
    public function get(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, Request $request, int $id): JsonResponse
    {
        $allowedDetails = ['organizations'];
        $details = $request->query->get('details');
        $detailGroups = !is_null($details) ? explode(',', $details) : [];
        if(!empty(array_diff($detailGroups, $allowedDetails))){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Requested details are invalid'
            ]);
        }

        $range = $request->query->get('range');
        $detailGroups = array_map(fn($group) => 'user-' . $group, $detailGroups);
        $groups = array_merge(['user'], $detailGroups);

        $user = $entityManager->getRepository(User::class)->find($id);
        if(!($user instanceof User)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'User not found'
            ]);
        }
        
        try{
            $responseData = $getterHelper->get($user, $groups, $range);
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }

        return $this->json($responseData);
    }

    #[Route('user', name: 'user_modify', methods: ['PATCH'])]
    public function modify(SetterHelperInterface $setterHelper, ValidatorInterface $validator, EntityManagerInterface $entityManager, Request $request):JsonResponse
    {

        $user = $this->getUser();

        if(!($user instanceof User)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'message' => 'Access Denied'
            ]);
        }

        try{
            $setterHelper->updateObjectSettings($user, $request->request->all(), [], ['Default']);
            $validationErrors = $setterHelper->getValidationErrors();
            
            $violations = $validator->validate($user, groups: $setterHelper->getValidationGroups());

            foreach ($violations as $violation) {
                $requestParameterName = $setterHelper->getPropertyRequestParameter($violation->getPropertyPath());
                $validationErrors[$requestParameterName] = $violation->getMessage();
            }            

            if(count($validationErrors) > 0){
                return $this->json([
                    'status' => 'Failure',
                    'message' => 'Validation Error',
                    'errors' => $validationErrors
                ]);
            }

            $setterHelper->runPostValidationTasks();
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }
        catch(MailingHelperException){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Server Error'
            ]);
        }

        $entityManager->flush();

        return $this->json([
            'status' => 'Success',
            'message' => 'Account settings modified successfully'
        ]);
    }

    #[Route('user', name: 'user_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager){
        $user = $this->getUser();

        if(!($user instanceof User)){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => 'Access Denied'
            ]);
        }

        $orphanedOrganizations = $user->getOrganizationAssignments()->filter(function($element){
            return $element->hasRoles(['ADMIN']) && $element->getOrganization()->getAdmins()->count() < 2;
        })
        ->map(function($element){
            return $element->getOrganization();
        });

        foreach($orphanedOrganizations as $organization){
            $entityManager->remove($organization);
        }

        $entityManager->remove($user);
        $entityManager->flush();


        return $this->json([
            'status' => 'Success',
            'message' => 'Account removed successfully'
        ]);
    }

    #[Route('users', name: 'users_get', methods: ['GET'])]
    public function getUsers(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, Request $request): JsonResponse
    {
        $filter = $request->query->get('filter');
        $range = $request->query->get('range');

        $users = $entityManager->getRepository(User::class)->findByPartialIdentifier($filter ?? '');
        
        try{
            $responseData = $getterHelper->getCollection($users, ['users'], $range);
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'status' => 'Failure',
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }

        return $this->json($responseData);
    }


}
