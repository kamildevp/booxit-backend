<?php

declare(strict_types=1);

namespace App\Controller;

use App\Documentation\Response\NotFoundResponseDoc;
use App\Documentation\Response\PaginatorResponseDoc;
use App\Documentation\Response\ServerErrorResponseDoc;
use App\Documentation\Response\SuccessResponseDoc;
use App\Documentation\Response\UnauthorizedResponseDoc;
use App\Documentation\Response\ValidationErrorResponseDoc;
use App\DTO\EmailConfirmation\VerifyEmailConfirmationDTO;
use App\DTO\User\UserChangePasswordDTO;
use App\DTO\User\UserCreateDTO;
use App\DTO\User\UserListQueryDTO;
use App\DTO\User\UserPatchDTO;
use App\DTO\User\UserResetPasswordDTO;
use App\DTO\User\UserResetPasswordRequestDTO;
use App\Entity\User;
use App\Enum\User\UserNormalizerGroup;
use App\Repository\UserRepository;
use App\Response\ApiResponse;
use App\Response\ResourceCreatedResponse;
use App\Response\SuccessResponse;
use App\Response\ValidationFailedResponse;
use App\Service\Auth\Attribute\RestrictedAccess;
use App\Service\Auth\AuthServiceInterface;
use App\Service\Entity\UserService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[ServerErrorResponseDoc]
#[OA\Tag('User')]
class UserController extends AbstractController
{
    #[OA\Post(
        summary: 'Create a new user',
        description: 'Registers a new unverified user and sends an email containing a verification link. 
        The link is generated using the specified **verification_handler**, which must match one of the predefined handlers to ensure it points to a trusted domain. 
        Users must verify their email address before they can log in. Unverified accounts are automatically deleted after 24 hours.'
    )]
    #[SuccessResponseDoc(
        statusCode: 201,
        description: 'Created User',
        dataModel: User::class,
        dataModelGroups: UserNormalizerGroup::PRIVATE
    )]
    #[ValidationErrorResponseDoc]
    #[Route('user', name: 'user_new', methods: ['POST'])]
    public function create(
        UserService $userService, 
        #[MapRequestPayload] UserCreateDTO $dto,
        EntitySerializerInterface $entitySerializer,   
    ): ResourceCreatedResponse
    {
        $user = $userService->createUser($dto);
        $responseData = $entitySerializer->normalize($user, UserNormalizerGroup::PRIVATE->normalizationGroups());
        
        return new ResourceCreatedResponse($responseData);
    }

    #[OA\Post(
        summary: 'Verify user',
        description: 'Confirms a user’s email address using the verification parameters provided in the link sent to that address. 
        This endpoint should be called by the verification handler specified during user registration or email change.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Verification Successful'])]
    #[ValidationErrorResponseDoc]
    #[Route('user/verify', name: 'user_verify', methods: ['POST'])]
    public function verify(UserService $userService, #[MapRequestPayload] VerifyEmailConfirmationDTO $dto): ApiResponse
    {
        $verified = $userService->verifyUserEmail($dto);

        return $verified ? 
            new SuccessResponse(['message' => 'Verification Successful']) : 
            new ValidationFailedResponse('Verification Failed');
    }

    #[OA\Get(
        summary: 'Get current user',
        description: 'Returns the data of the authenticated user based on the access token.'
    )]
    #[SuccessResponseDoc(
        description: 'Current User Data',
        dataModel: User::class,
        dataModelGroups: UserNormalizerGroup::PRIVATE
    )]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess]
    #[Route('user/me', name: 'user_me_get', methods: ['GET'])]
    public function me(EntitySerializerInterface $entitySerializer): SuccessResponse
    {
        $user = $this->getUser();
        $responseData = $entitySerializer->normalize($user, UserNormalizerGroup::PRIVATE->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[OA\Get(
        summary: 'Get user',
        description: 'Returns the public data of the specified user.'
    )]
    #[SuccessResponseDoc(
        description: 'Requested User Data',
        dataModel: User::class,
        dataModelGroups: UserNormalizerGroup::PUBLIC
    )]
    #[NotFoundResponseDoc('User not found')]
    #[Route('user/{user}', name: 'user_get', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function get(EntitySerializerInterface $entitySerializer, User $user): SuccessResponse
    {
        $responseData = $entitySerializer->normalize($user, UserNormalizerGroup::PUBLIC->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[OA\Patch(
        summary: 'Update current user',
        description: 'Updates the authenticated user’s data.  
        **Important:** If the email address is changed, a verification link will be sent to the new address.  
        The change takes effect only after the new email has been verified.'
    )]
    #[SuccessResponseDoc(
        description: 'Patched User Data',
        dataModel: User::class,
        dataModelGroups: UserNormalizerGroup::PRIVATE
    )]
    #[ValidationErrorResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess]
    #[Route('user/me', name: 'user_me_patch', methods: ['PATCH'])]
    public function patch(
        UserService $userService, 
        EntitySerializerInterface $entitySerializer, 
        #[MapRequestPayload] UserPatchDTO $dto
    ): SuccessResponse
    {
        $user = $this->getUser();
        $user = $userService->patchUser($user, $dto);

        $responseData = $entitySerializer->normalize($user, UserNormalizerGroup::PRIVATE->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[OA\Patch(
        summary: 'Change password',
        description: 'Changes the authenticated user’s password.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Password changed successfully'])]
    #[ValidationErrorResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess]
    #[Route('user/change_password', name: 'user_change_password', methods: ['PATCH'])]
    public function changePassword(
        AuthServiceInterface $authService, 
        UserService $userService, 
        #[MapRequestPayload] UserChangePasswordDTO $dto
    ): SuccessResponse
    {
        $user = $this->getUser();
        $userRefreshToken = $authService->getRefreshTokenUsedByCurrentUser();
        $userService->changeUserPassword($user, $dto->password, $dto->logoutOtherSessions, $userRefreshToken);

        return new SuccessResponse(['message' => 'Password changed successfully']);
    }

    #[OA\Delete(
        summary: 'Delete current user account',
        description: 'Deletes the authenticated user’s account.  
        **Important:** The account is soft-deleted, which means a new account cannot be registered with the same email address.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'User removed successfully'])]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess]
    #[Route('user/me', name: 'user_me_delete', methods: ['DELETE'])]
    public function delete(UserRepository $userRepository): SuccessResponse
    {
        $user = $this->getUser();
        $userRepository->remove($user, true);
        
        return new SuccessResponse(['message' => 'User removed successfully']);
    }

    #[OA\Get(
        summary: 'List users',
        description: 'Retrieves a paginated list of registered users with their public information.'
    )]
    #[PaginatorResponseDoc(
        description: 'Paginated users list', 
        dataModel: User::class,
        dataModelGroups: UserNormalizerGroup::PUBLIC
    )]
    #[ValidationErrorResponseDoc]
    #[Route('user', name: 'user_list', methods: ['GET'])]
    public function list(
        UserRepository $userRepository, 
        EntitySerializerInterface $entitySerializer, 
        #[MapQueryString] UserListQueryDTO $queryDTO = new UserListQueryDTO,
    ): SuccessResponse
    {
        $paginationResult = $userRepository->paginate($queryDTO);
        $formattedItems = $entitySerializer->normalize($paginationResult->getItems(), UserNormalizerGroup::PUBLIC->normalizationGroups());
        $paginationResult->setItems($formattedItems);

        return new SuccessResponse($paginationResult);
    }

    #[OA\Post(
        summary: 'Request password reset',
        description: 'If the provided email address matches a registered user, a password reset link valid for 24 hours will be sent to that address.  
        **Important:** If a valid reset link already exists for the user, a new email will not be sent.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'If user with specified email exists, password reset link was sent to specified email'])]
    #[ValidationErrorResponseDoc]
    #[Route('user/reset_password_request', name: 'user_reset_password_request', methods: ['POST'])]
    public function resetPasswordRequest(UserService $userService, #[MapRequestPayload] UserResetPasswordRequestDTO $dto): SuccessResponse
    {
        $userService->handleResetUserPasswordRequest($dto);

        return new SuccessResponse(['message' => 'If user with specified email exists, password reset link was sent to specified email']);
    }

    #[OA\Patch(
        summary: 'Reset user password',
        description: 'Resets the user’s password using the parameters included in a valid password reset link.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Password reset successful'])]
    #[ValidationErrorResponseDoc]
    #[Route('user/reset_password', name: 'user_reset_password', methods: ['PATCH'])]
    public function resetPassword(UserService $userService, #[MapRequestPayload] UserResetPasswordDTO $dto): ApiResponse
    {
        $result = $userService->resetUserPassword($dto);

        return $result ? 
            new SuccessResponse(['message' => 'Password reset successful']) : 
            new ValidationFailedResponse('Password reset failed');
    }

}
