<?php

namespace App\Controller;

use App\Entity\EmailConfirmation;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\MailingHelperException;
use App\Service\GetterHelper\GetterHelperInterface;
use App\Service\MailingHelper\MailingHelper;
use App\Service\SetterHelper\SetterHelperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class UserController extends AbstractApiController
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

            foreach ($violations as $violation) {
                $requestParameterName = $setterHelper->getPropertyRequestParameter($violation->getPropertyPath());
                $validationErrors[$requestParameterName] = $violation->getMessage();
            }

            if(count($validationErrors) > 0){
                return $this->newApiResponse(status: 'fail', data: ['message' => 'Validation error', 'errors' => $validationErrors], code: 400);
            }

            $setterHelper->runPostValidationTasks();

        }
        catch(InvalidRequestException){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => $setterHelper->getRequestErrors()], code: 400);
        }

        // $user->setVerified(false);   //uncomment when mailing provider is available
        // $user->setExpiryDate(new \DateTime('+1 days')); //uncomment when mailing provider is available
        $user->setVerified(true); //remove when mailing provider is available
        $user->setExpiryDate(null); //remove when mailing provider is available

        $entityManager->persist($user);
        $entityManager->flush();

        try{
            // $mailingHelper->newEmailVerification($user, $user->getEmail()); //uncomment when mailing provider is available
        }
        catch(MailingHelperException){
            $entityManager->remove($user);
            $entityManager->flush();
            return $this->newApiResponse(status: 'error', data: ['message' => 'Mailing provider error'], code: 500);
        }
        return $this->newApiResponse(data: ['message' => 'Account created successfully'], code: 201);

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

            $refreshTokens = $entityManager->getRepository(RefreshToken::class)->findBy(['username' => $user->getEmail()]);
            foreach($refreshTokens as $token){
                $entityManager->remove($token);
            }

            $user->setEmail($emailConfirmation->getEmail());
            $user->setVerified(true);
            $user->setExpiryDate(null);
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

    #[Route('user/{userId}', name: 'user_get', methods: ['GET'])]
    public function get(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, Request $request, int $userId): JsonResponse
    {
        $allowedDetails = ['organizations'];
        $details = $request->query->get('details');
        $detailGroups = !is_null($details) ? explode(',', $details) : [];
        if(!empty(array_diff($detailGroups, $allowedDetails))){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => ['details' => 'Requested details are invalid']], code: 400);
        }

        $range = $request->query->get('range');
        $detailGroups = array_map(fn($group) => 'user-' . $group, $detailGroups);
        $groups = array_merge(['user'], $detailGroups);

        if($userId === 'logged_in'){
            $user = $this->getUser();
        }

        $user = $entityManager->getRepository(User::class)->find($userId);
        if(!($user instanceof User)){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'User not found'], code: 404);
        }
        
        try{
            $responseData = $getterHelper->get($user, $groups, $range);
        }
        catch(InvalidRequestException){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => $getterHelper->getRequestErrors()], code: 400);
        }

        return $this->newApiResponse(data: $responseData);
    }

    #[Route('user', name: 'user_modify', methods: ['PATCH'])]
    public function modify(SetterHelperInterface $setterHelper, ValidatorInterface $validator, EntityManagerInterface $entityManager, Request $request):JsonResponse
    {
        $user = $this->getUser();

        if(!($user instanceof User)){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 401);
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
                return $this->newApiResponse(status: 'fail', data: ['message' => 'Validation error', 'errors' => $validationErrors], code: 400);
            }

            $setterHelper->runPostValidationTasks();
        }
        catch(InvalidRequestException){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => $setterHelper->getRequestErrors()], code: 400);
        }
        catch(MailingHelperException){
            return $this->newApiResponse(status: 'error', data: ['message' => 'Mailing provider error'], code: 500);
        }

        $entityManager->flush();

        return $this->newApiResponse(data: ['message' => 'Account settings modified successfully']);
    }

    #[Route('user', name: 'user_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager){
        $user = $this->getUser();

        if(!($user instanceof User)){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Access denied'], code: 401);
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

        $refreshTokens = $entityManager->getRepository(RefreshToken::class)->findBy(['username' => $user->getEmail()]);
        foreach($refreshTokens as $token){
            $entityManager->remove($token);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->newApiResponse(data: ['message' => 'Account removed successfully']);
    }

    #[Route('user', name: 'users_get', methods: ['GET'])]
    public function getUsers(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, Request $request): JsonResponse
    {
        $filter = $request->query->get('filter');
        $range = $request->query->get('range');

        $users = $entityManager->getRepository(User::class)->findByPartialIdentifier($filter ?? '');
        
        try{
            $responseData = $getterHelper->getCollection($users, ['users'], $range);
        }
        catch(InvalidRequestException $e){
            return $this->newApiResponse(status: 'fail', data: ['message' => 'Invalid request', 'errors' => $getterHelper->getRequestErrors()], code: 400);
        }

        return $this->newApiResponse(data: $responseData);
    }


}
