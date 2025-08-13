<?php

namespace App\Service\Auth;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\HttpFoundation\Request;

interface RouteGuardInterface
{
    public function validateAccess(AbstractController|RedirectController $controller, Request $request, ?string $methodName = null): void;

    public function getAuthorizedUserOrFail(): User;
}