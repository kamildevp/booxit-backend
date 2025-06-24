<?php

namespace App\Service\Auth;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

interface RouteGuardInterface
{
    public function validateAccess(AbstractController $controller, string $methodName, Request $request): void;

    public function getAuthorizedUserOrFail(): User;
}