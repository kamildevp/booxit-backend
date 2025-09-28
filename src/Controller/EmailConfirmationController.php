<?php

namespace App\Controller;

use App\Documentation\Response\ServerErrorResponseDoc;
use App\Documentation\Response\SuccessResponseDoc;
use App\Documentation\Response\ValidationErrorResponseDoc;
use App\DTO\EmailConfirmation\ValidateEmailConfirmationDTO;
use App\Response\SuccessResponse;
use App\Response\ValidationFailedResponse;
use App\Service\Entity\EmailConfirmationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[ServerErrorResponseDoc()]
#[OA\Tag('EmailConfirmation')]
class EmailConfirmationController extends AbstractController
{
    #[OA\Get(
        summary: 'Check email confirmation validity',
        description: 'Checks whether the provided email confirmation parameters are valid. 
        This endpoint only verifies the validity of the email confirmation and does not perform the actual verification process, 
        which should be handled by other dedicated verification endpoints. 
        It is intended for use when the client needs to confirm the validity of an email confirmation before initiating the full verification process.'
    )]
    #[SuccessResponseDoc(dataExample: ['valid' => true])]
    #[ValidationErrorResponseDoc]
    #[Route('email_confirmation/validate', name: 'email_confirmation_validate', methods: ['GET'])]
    public function validate(EmailConfirmationService $emailConfirmationService, #[MapQueryString] ValidateEmailConfirmationDTO $dto)
    {
        $valid = $emailConfirmationService->validateEmailConfirmation($dto);

        return $valid ? new SuccessResponse(['valid' => true]) : new ValidationFailedResponse();
    }
}
