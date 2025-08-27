<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Entity\User;
use Nelmio\ApiDocBundle\Controller\DocumentationController;
use Nelmio\ApiDocBundle\Controller\SwaggerUiController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\HttpFoundation\Request;

interface RouteGuardInterface
{
    public function validateAccess(
        AbstractController|RedirectController|DocumentationController|SwaggerUiController $controller, 
        Request $request, 
        array $controllerArguments,
        ?string $methodName = null, 
    ): void;

    public function getAuthorizedUserOrFail(): User;
}