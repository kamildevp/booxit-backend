<?php

namespace App\Controller;

use App\Entity\Service;
use App\Exceptions\InvalidRequestException;
use App\Service\GetterHelper\GetterHelperInterface;
use App\Service\SetterHelper\SetterHelperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ServiceController extends AbstractController
{
    #[Route('service', name: 'service_new', methods: ['POST'])]
    public function new(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper, 
        ValidatorInterface $validator, 
        Request $request
        ): JsonResponse
    {

        $service = new Service();

        try{
            $setterHelper->updateObjectSettings($service, $request->request->all(), true, ['Default', 'initOnly']);
            $violations = $validator->validate($service, groups: $setterHelper->getValidationGroups());
            
            if(count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $requestParameterName = $setterHelper->getPropertyRequestParameter($violation->getPropertyPath());
                    $errors[$requestParameterName][] = $violation->getMessage();
                }
                return $this->json([
                    'message' => 'Validation Error',
                    'errors' => $errors
                ]);
            }
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }

        $setterHelper->runPostValidationTasks();

        $entityManager->persist($service);
        $entityManager->flush();

        return $this->json([
            'message' => 'Service created successfully'
        ]);
    }

    #[Route('service/{serviceId}', name: 'service_get', methods: ['GET'])]
    public function get(
        EntityManagerInterface $entityManager, 
        GetterHelperInterface $getterHelper, 
        int $serviceId
        ): JsonResponse
    {

        $service = $entityManager->getRepository(Service::class)->find($serviceId);
        if(!($service instanceof Service)){
            return $this->json([
                'message' => 'Service not found'
            ]);
        }

        $responseData = $getterHelper->get($service);

        return $this->json($responseData);
    }

    #[Route('service/{serviceId}', name: 'service_modify', methods: ['PATCH'])]
    public function modify(
        EntityManagerInterface $entityManager, 
        SetterHelperInterface $setterHelper, 
        ValidatorInterface $validator, 
        Request $request, 
        int $serviceId
        ): JsonResponse
    {

        $service = $entityManager->getRepository(Service::class)->find($serviceId);
        if(!($service instanceof Service)){
            return $this->json([
                'message' => 'Service not found'
            ]);
        }

        try{
            $setterHelper->updateObjectSettings($service, $request->request->all());
            $violations = $validator->validate($service, groups: $setterHelper->getValidationGroups());
            
            if(count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $requestParameterName = $setterHelper->getPropertyRequestParameter($violation->getPropertyPath());
                    $errors[$requestParameterName][] = $violation->getMessage();
                }
                return $this->json([
                    'message' => 'Validation Error',
                    'errors' => $errors
                ]);
            }
        }
        catch(InvalidRequestException $e){
            return $this->json([
                'message' => 'Invalid Request',
                'errors' => $e->getMessage()
            ]);
        }

        $setterHelper->runPostValidationTasks();

        $entityManager->flush();

        return $this->json([
            'message' => 'Service modified successfully'
        ]);
    }

    #[Route('service/{serviceId}', name: 'service_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $serviceId): JsonResponse
    {
        $service = $entityManager->getRepository(Service::class)->find($serviceId);
        if(!($service instanceof Service)){
            return $this->json([
                'message' => 'Service not found'
            ]);
        }

        $organization = $service->getOrganization();

        $currentUser = $this->getUser();
        if(!($currentUser && $organization->hasMember($currentUser) && $organization->getMember($currentUser)->hasRoles(['ADMIN']))){
            return $this->json([
                'message' => 'Access Denied'
            ]);
        }
        
        $entityManager->remove($service);
        $entityManager->flush();

        return $this->json([
            'message' => 'Service removed successfully'
        ]);
    }

}
