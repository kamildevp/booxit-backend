<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractApiController extends AbstractController
{
    public function newApiResponse(string $status = 'success', $data = null, int $code = 200):JsonResponse
    {
        return $this->json([
            'status' => $status,
            'data' => $data
        ], $code);
    }
}