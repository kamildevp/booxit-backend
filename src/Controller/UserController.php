<?php

namespace App\Controller;

use App\Entity\EmailConfirmation;
use App\Entity\User;
use App\Exceptions\InvalidRequestException;
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
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager, 
        // MailingHelper $mailingHelper, //uncomment when mailing provider is available
        Request $request
        ): JsonResponse
    {
        try{
            (new RequestValidator)->validateRequest($request->request->all(), ['name', 'email', 'password']);
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'message' => 'Invalid Request',
                'error' => $e->getMessage()
            ]);
        }

        $user = new User();
        $user->setName($request->get('name'));
        $user->setEmail($request->get('email'));
        $user->setPlainPassword($request->get('password'));

        $violations = $validator->validate($user, groups: ['Default', 'plainPassword']);
        
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

        $password = $passwordHasher->hashPassword($user, $user->getPlainPassword());
        $user->setPassword($password);
        // $user->setVerified(false);   //uncomment when mailing provider is available
        $user->setVerified(true); //remove when mailing provider is available

        $entityManager->persist($user);
        $entityManager->flush();

        // $mailingHelper->newEmailVerification($user, $user->getEmail()); //uncomment when mailing provider is available

        return $this->json([
            'message' => 'Account was created successfully'
        ]);
    }

    #[Route('user/verify', name: 'user_verify', methods: ['GET'])]
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
    public function get(EntityManagerInterface $entityManager, GetterHelperInterface $getterHelper, int $id): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if(!$user){
            $responseData['message'] = 'Account not found';
        }
        else{
            $responseData = $getterHelper->get($user);
        }

        return $this->json($responseData);
    }

    

    #[Route('user', name: 'user_modify', methods: ['PATCH'])]
    public function modify(SetterHelperInterface $setterHelper, ValidatorInterface $validator, EntityManagerInterface $entityManager, Request $request):JsonResponse
    {

        $user = $this->getUser();

        if(!($user instanceof User)){
            return $this->json([
                'message' => 'Access Denied'
            ]);
        }

        $requestParameters = $request->request->all();
        if(empty($requestParameters)){
            return $this->json([
                'message' => 'Invalid Request',
                'errors' => 'Request has no parameters'
            ]);
        }

        try{
            
            $setterHelper->updateObjectSettings($user, $requestParameters);

            $violations = $validator->validate($user, groups: $setterHelper->getValidationGroups());
            
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

        $responseData =  $this->json([
            'message' => 'Settings changed successfully',
        ]);
        
        return $responseData;

    }

    #[Route('user/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id){
        $user = $entityManager->getRepository(User::class)->find($id);


        switch(true){
            case !$user:
                $responseData['message'] = 'Account not found';
                break;
            case $user !== $this->getUser():
                $responseData['message'] = 'Access denied';
                break;
            case $user && $user === $this->getUser():
                $entityManager->remove($user);
                $entityManager->flush();
                $responseData['message'] = 'Account deleted successfully';
                break;
        }

        return $this->json($responseData);
    }




}
