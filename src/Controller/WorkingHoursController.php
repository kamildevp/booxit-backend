<?php

declare(strict_types=1);

namespace App\Controller;

use App\Documentation\Response\TimeWindowsPerDateResponseDoc;
use App\Documentation\Response\ForbiddenResponseDoc;
use App\Documentation\Response\NotFoundResponseDoc;
use App\Documentation\Response\ServerErrorResponseDoc;
use App\Documentation\Response\SuccessResponseDoc;
use App\Documentation\Response\UnauthorizedResponseDoc;
use App\Documentation\Response\ValidationErrorResponseDoc;
use App\Documentation\Response\TimeWindowsPerWeekdayResponseDoc;
use App\DTO\WorkingHours\CustomWorkingHoursGetDTO;
use App\DTO\WorkingHours\CustomWorkingHoursUpdateDTO;
use App\DTO\WorkingHours\WeeklyWorkingHoursUpdateDTO;
use App\Entity\Schedule;
use App\Repository\CustomTimeWindowRepository;
use App\Response\SuccessResponse;
use App\Service\Auth\AccessRule\ScheduleWritePrivilegesRule;
use App\Service\Auth\Attribute\RestrictedAccess;
use App\Service\Entity\WorkingHoursService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use DateTimeInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

#[ServerErrorResponseDoc]
#[OA\Tag('WorkingHours')]
class WorkingHoursController extends AbstractController
{
    #[OA\Put(
        summary: 'Update schedule weekly working hours',
        description: 'Updates schedule weekly working hours.
        <br><br>**Note:** Working hours that cross midnight are supported. 
        For example, a time window defined as (start_time=15:00, end_time=02:00) will continue into the next day and end at 02:00.
        </br></br>**Important:** This action can only be performed by organization admin or schedule assignee with *WRITE* privileges.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Schedule weekly working hours have been updated'])]
    #[NotFoundResponseDoc('Schedule not found')]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ScheduleWritePrivilegesRule::class)]
    #[Route('schedules/{schedule}/weekly-working-hours', name: 'schedule_weekly_working_hours_update', methods: ['PUT'], requirements: ['schedule' => '\d+'])]
    public function updateScheduleWeeklyWorkingHours(
        Schedule $schedule,
        #[MapRequestPayload] WeeklyWorkingHoursUpdateDTO $dto,
        WorkingHoursService $workingHoursService,
    ): SuccessResponse
    {
        $workingHoursService->setScheduleWeeklyWorkingHours($schedule, $dto);

        return new SuccessResponse(['message' => 'Schedule weekly working hours have been updated']);
    }

    #[OA\Get(
        summary: 'Get schedule weekly working hours',
        description: 'Returns schedule weekly working hours.'
    )]
    #[TimeWindowsPerWeekdayResponseDoc]
    #[NotFoundResponseDoc('Schedule not found')]
    #[Route('schedules/{schedule}/weekly-working-hours', name: 'schedule_weekly_working_hours_get', methods: ['GET'], requirements: ['schedule' => '\d+'])]
    public function getScheduleWeeklyWorkingHours(
        Schedule $schedule, 
        EntitySerializerInterface $entitySerializer,
        WorkingHoursService $workingHoursService,    
    ): SuccessResponse
    {
        $workingHours = $workingHoursService->getScheduleWeeklyWorkingHours($schedule);
        $responseData = $entitySerializer->normalize($workingHours, []);

        return new SuccessResponse($responseData);
    }

    #[OA\Put(
        summary: 'Update custom schedule working hours',
        description: 'Updates custom schedule working hours for specific date.
        <br><br>**Note:** Working hours that cross midnight are supported. 
        For example, a time window defined as (start_time=15:00, end_time=02:00) will continue into the next day and end at 02:00.
        </br></br>**Important:** This action can only be performed by organization admin or schedule assignee with *WRITE* privileges.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Schedule custom working hours have been updated'])]
    #[NotFoundResponseDoc('Schedule not found')]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ScheduleWritePrivilegesRule::class)]
    #[Route('schedules/{schedule}/custom-working-hours', name: 'schedule_custom_working_hours_update', methods: ['PUT'], requirements: ['schedule' => '\d+'])]
    public function updateScheduleCustomWorkingHours(
        Schedule $schedule,
        #[MapRequestPayload] CustomWorkingHoursUpdateDTO $dto,
        WorkingHoursService $workingHoursService,
    ): SuccessResponse
    {
        $workingHoursService->setScheduleCustomWorkingHours($schedule, $dto);

        return new SuccessResponse(['message' => 'Schedule custom working hours have been updated']);
    }

    #[OA\Get(
        summary: 'Get schedule custom working hours',
        description: 'Returns schedule custom working hours for the specified date range (up to one month). If no range is provided, the current week is used by default.'
    )]
    #[TimeWindowsPerDateResponseDoc]
    #[NotFoundResponseDoc('Schedule not found')]
    #[Route('schedules/{schedule}/custom-working-hours', name: 'schedule_custom_working_hours_get', methods: ['GET'], requirements: ['schedule' => '\d+'])]
    public function getScheduleCustomWorkingHours(
        Schedule $schedule, 
        EntitySerializerInterface $entitySerializer,
        WorkingHoursService $workingHoursService, 
        #[MapQueryString] CustomWorkingHoursGetDTO $dto = new CustomWorkingHoursGetDTO,
    ): SuccessResponse
    {
        $workingHours = $workingHoursService->getScheduleCustomWorkingHours($schedule, $dto->dateFrom, $dto->dateTo);
        $responseData = $entitySerializer->normalize($workingHours, []);

        return new SuccessResponse($responseData);
    }
}