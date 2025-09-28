<?php

namespace App\Controller;

use App\DTO\EmailConfirmation\ValidateEmailConfirmationDTO;
use App\Response\SuccessResponse;
use App\Response\ValidationFailedResponse;
use App\Service\Entity\EmailConfirmationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

class EmailConfirmationController extends AbstractController
{
    #[Route('email_confirmation/validate', name: 'email_confirmation_validate', methods: ['GET'])]
    public function verify(EmailConfirmationService $emailConfirmationService, #[MapQueryString] ValidateEmailConfirmationDTO $dto)
    {
        $valid = $emailConfirmationService->validateEmailConfirmation($dto);

        return $valid ? new SuccessResponse(['valid' => $valid]) : new ValidationFailedResponse();
    }
}
