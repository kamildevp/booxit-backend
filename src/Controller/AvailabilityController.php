<?php

declare(strict_types=1);

namespace App\Controller;

use App\Documentation\Response\TimeWindowsPerDateResponseDoc;
use App\Documentation\Response\NotFoundResponseDoc;
use App\Documentation\Response\ServerErrorResponseDoc;
use App\DTO\WorkingHours\ScheduleAvailabilityGetDTO;
use App\Entity\Schedule;
use App\Response\SuccessResponse;
use App\Service\Entity\AvailabilityService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

#[ServerErrorResponseDoc]
#[OA\Tag('Availability')]
class AvailabilityController extends AbstractController
{
    #[OA\Get(
        summary: 'Get schedule availability',
        description: 'Returns schedule availability for the specified date range (up to one month). If no range is provided, the current week is used by default.'
    )]
    #[TimeWindowsPerDateResponseDoc]
    #[NotFoundResponseDoc('Schedule not found')]
    #[Route('schedules/{schedule}/availability', name: 'schedule_availability_get', methods: ['GET'], requirements: ['schedule' => '\d+'])]
    public function getScheduleAvailability(
        Schedule $schedule, 
        EntitySerializerInterface $entitySerializer,
        AvailabilityService $availabilityService,
        #[MapQueryString] ScheduleAvailabilityGetDTO $dto = new ScheduleAvailabilityGetDTO
    ): SuccessResponse
    {
        $availability = $availabilityService->getScheduleAvailability($schedule, $dto);
        $responseData = $entitySerializer->normalize($availability, []);

        return new SuccessResponse($responseData);
    }
}