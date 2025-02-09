<?php

namespace App\Controller;

use App\Enum\User\UserGetterGroup;
use App\Enum\User\UserSetterGroup;
use App\Enum\ViewType;
use App\Exceptions\VerifyEmailException;
use App\Repository\UserRepository;
use App\Response\ResourceCreatedResponse;
use App\Response\SuccessResponse;
use App\Service\Auth\Attribute\RestrictedAccess;
use App\Service\Entity\UserService;
use App\Service\EntityHandler\EntityHandlerInterface;
use App\Service\GetterHelper\GetterHelperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    #[Route('user', name: 'user_new', methods: ['POST'])]
    public function new(UserService $userService, Request $request): JsonResponse
    {
        $user = $userService->createUser($request->request->all());

        return new ResourceCreatedResponse($user);
    }

    #[Route('user/verify', name: 'user_verify', methods: ['GET'])]
    public function verify(UserService $userService, Request $request)
    {
        try{
            $emailConfirmationId = (int)$request->get('id');
            $userService->verifyUserEmail($emailConfirmationId, $request->getUri());
            $view = ViewType::EMAIL_VERIFICATION_SUCCESS->getView();
        }
        catch(VerifyEmailException $e){
            $view = ViewType::EMAIL_VERIFICATION_FAIL->getView();
            $view->setParam('description', $e->getMessage());
        }

        return $this->render($view->getTemplate(), $view->getParams());
    }

    #[RestrictedAccess]
    #[Route('user/me', name: 'user_me_get', methods: ['GET'])]
    public function me(GetterHelperInterface $getterHelper): JsonResponse
    {
        $user = $this->getUser();
        $responseData = $getterHelper->get($user, [UserGetterGroup::ALL->value]);

        return new SuccessResponse($responseData);
    }

    #[Route('user/{userId}', name: 'user_get', methods: ['GET'])]
    public function get(UserRepository $userRepository, GetterHelperInterface $getterHelper, int $userId): JsonResponse
    {
        $user = $userRepository->findOrFail($userId);
        $responseData = $getterHelper->get($user, [UserGetterGroup::PUBLIC->value]);

        return new SuccessResponse($responseData);
    }

    #[RestrictedAccess]
    #[Route('user/me', name: 'user_me_patch', methods: ['PATCH'])]
    public function modify(EntityHandlerInterface $entityHandler, GetterHelperInterface $getterHelper, UserRepository $userRepository, Request $request): JsonResponse
    {
        $user = $this->getUser();
        $entityHandler->parseParamsToEntity($user, $request->request->all(), [UserSetterGroup::PATCH->value]);
        $userRepository->save($user, true);
        $responseData = $getterHelper->get($user, [UserGetterGroup::ALL->value]);

        return new SuccessResponse($responseData);
    }

    #[RestrictedAccess]
    #[Route('user', name: 'user_me_delete', methods: ['DELETE'])]
    public function delete(UserRepository $userRepository){
        $user = $this->getUser();

        $userRepository->remove($user, true);
        
        return new SuccessResponse(['message' => 'User removed successfully']);
    }

    #[Route('user', name: 'users_get', methods: ['GET'])]
    public function getUsers(UserRepository $userRepository, GetterHelperInterface $getterHelper, Request $request): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $paginationResult = $userRepository->paginate($page);
        $formattedItems = $getterHelper->getCollection($paginationResult->getItems(), [UserGetterGroup::PUBLIC->value]);
        $paginationResult->setItems($formattedItems);

        return new SuccessResponse($paginationResult);
    }


}
