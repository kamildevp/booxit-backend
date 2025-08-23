<?php

namespace App\Controller;

use App\Documentation\Response\ServerErrorResponseDoc;
use App\Documentation\Response\SuccessResponseDoc;
use App\Documentation\Response\UnauthorizedResponseDoc;
use App\Documentation\Response\ValidationErrorResponseDoc;
use App\DTO\Auth\AuthLogoutDTO;
use App\Response\SuccessResponse;
use App\Service\Auth\Attribute\RestrictedAccess;
use App\Service\Auth\AuthServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[ServerErrorResponseDoc]
#[OA\Tag('Auth')]
class AuthController extends AbstractController
{
    #[OA\Post(
        summary: 'Log out the authenticated user',
        description: 'Revokes the refresh token associated with the provided access token, or all refresh tokens issued to the user, depending on the options passed.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Logged out successfully'])]
    #[ValidationErrorResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess]
    #[Route('auth/logout', name: 'auth_logout', methods: ['POST'])]
    public function logout(
        AuthServiceInterface $authService, 
        #[MapRequestPayload] AuthLogoutDTO $dto 
    ): SuccessResponse
    {        
        $authService->logoutCurrentUser($dto);
        return new SuccessResponse(['message' => 'Logged out successfully']);
    }
}
