<?php

namespace App\Controller;

use App\Documentation\Response\NotFoundResponseDoc;
use App\Documentation\Response\PaginatorResponseDoc;
use App\Documentation\Response\ServerErrorResponseDoc;
use App\Documentation\Response\SuccessResponseDoc;
use App\Documentation\Response\UnauthorizedResponseDoc;
use App\Documentation\Response\ValidationErrorResponseDoc;
use App\DTO\EmailConfirmation\VerifyEmailConfirmationDTO;
use App\DTO\PaginationDTO;
use App\DTO\User\UserChangePasswordDTO;
use App\DTO\User\UserCreateDTO;
use App\DTO\User\UserListFiltersDTO;
use App\DTO\User\UserListOrderDTO;
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

    #[SuccessResponseDoc(dataExample: ['message' => 'Verification Successful'])]
    #[ValidationErrorResponseDoc]
    #[Route('user/verify', name: 'user_verify', methods: ['POST'])]
    public function verify(UserService $userService, #[MapRequestPayload] VerifyEmailConfirmationDTO $dto)
    {
        $verified = $userService->verifyUserEmail($dto);

        return $verified ? 
            new SuccessResponse(['message' => 'Verification Successful']) : 
            new ValidationFailedResponse('Verification Failed');
    }

    #[SuccessResponseDoc(
        description: 'Current User Data',
        dataModel: User::class,
        dataModelGroups: UserNormalizerGroup::PRIVATE
    )]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess]
    #[Route('user/me', name: 'user_me_get', methods: ['GET'])]
    public function me(EntitySerializerInterface $entitySerializer): ApiResponse
    {
        $user = $this->getUser();
        $responseData = $entitySerializer->normalize($user, UserNormalizerGroup::PRIVATE->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[SuccessResponseDoc(
        description: 'Requested User Data',
        dataModel: User::class,
        dataModelGroups: UserNormalizerGroup::PRIVATE
    )]
    #[NotFoundResponseDoc('User not found')]
    #[Route('user/{user}', name: 'user_get', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function get(EntitySerializerInterface $entitySerializer, User $user): ApiResponse
    {
        $responseData = $entitySerializer->normalize($user, UserNormalizerGroup::PUBLIC->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[SuccessResponseDoc(
        description: 'Patched User Data',
        dataModel: User::class,
        dataModelGroups: UserNormalizerGroup::PRIVATE
    )]
    #[ValidationErrorResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess]
    #[Route('user/me', name: 'user_me_patch', methods: ['PATCH'])]
    public function patch(UserService $userService, EntitySerializerInterface $entitySerializer, #[MapRequestPayload] UserPatchDTO $dto): ApiResponse
    {
        $user = $this->getUser();
        $user = $userService->patchUser($user, $dto);

        $responseData = $entitySerializer->normalize($user, UserNormalizerGroup::PRIVATE->normalizationGroups());

        return new SuccessResponse($responseData);
    }

    #[SuccessResponseDoc(dataExample: ['message' => 'Password changed successfully'])]
    #[ValidationErrorResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess]
    #[Route('user/change_password', name: 'user_change_password', methods: ['PATCH'])]
    public function changePassword(AuthServiceInterface $authService, UserService $userService, #[MapRequestPayload] UserChangePasswordDTO $dto): ApiResponse
    {
        $user = $this->getUser();
        $userRefreshToken = $authService->getRefreshTokenUsedByCurrentUser();
        $userService->changeUserPassword($user, $dto->password, $dto->logoutOtherSessions, $userRefreshToken);

        return new SuccessResponse(['message' => 'Password changed successfully']);
    }

    #[SuccessResponseDoc(dataExample: ['message' => 'User removed successfully'])]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess]
    #[Route('user/me', name: 'user_me_delete', methods: ['DELETE'])]
    public function delete(UserRepository $userRepository){
        $user = $this->getUser();
        $userRepository->remove($user, true);
        
        return new SuccessResponse(['message' => 'User removed successfully']);
    }

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
        #[MapQueryString] PaginationDTO $paginationDTO = new PaginationDTO,
        #[MapQueryString] UserListFiltersDTO $filtersDTO = new UserListFiltersDTO,
        #[MapQueryString] UserListOrderDTO $orderDTO = new UserListOrderDTO
    ): ApiResponse
    {
        $paginationResult = $userRepository->paginate($paginationDTO, $filtersDTO, $orderDTO);
        $formattedItems = $entitySerializer->normalize($paginationResult->getItems(), UserNormalizerGroup::PUBLIC->normalizationGroups());
        $paginationResult->setItems($formattedItems);

        return new SuccessResponse($paginationResult);
    }

    #[SuccessResponseDoc(dataExample: ['message' => 'If user with specified email exists, password reset link was sent to specified email'])]
    #[ValidationErrorResponseDoc]
    #[Route('user/reset_password_request', name: 'user_reset_password_request', methods: ['POST'])]
    public function resetPasswordRequest(UserService $userService, #[MapRequestPayload] UserResetPasswordRequestDTO $dto): ApiResponse
    {
        $userService->handleResetUserPasswordRequest($dto);

        return new SuccessResponse(['message' => 'If user with specified email exists, password reset link was sent to specified email']);
    }

    #[SuccessResponseDoc(dataExample: ['message' => 'Password reset successful'])]
    #[ValidationErrorResponseDoc]
    #[Route('user/reset_password', name: 'user_reset_password', methods: ['PATCH'])]
    public function resetPassword(UserService $userService, #[MapRequestPayload] UserResetPasswordDTO $dto)
    {
        $result = $userService->resetUserPassword($dto);

        return $result ? 
            new SuccessResponse(['message' => 'Password reset successful']) : 
            new ValidationFailedResponse('Password reset failed');
    }

}
