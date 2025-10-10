<?php

declare(strict_types=1);

namespace App\Controller;

use App\Documentation\Response\ForbiddenResponseDoc;
use App\Documentation\Response\NotFoundResponseDoc;
use App\Documentation\Response\ServerErrorResponseDoc;
use App\Documentation\Response\SuccessResponseDoc;
use App\Documentation\Response\UnauthorizedResponseDoc;
use App\Documentation\Response\ValidationErrorResponseDoc;
use App\Documentation\Response\WeeklyWorkingHoursResponseDoc;
use App\DTO\WorkingHours\CustomWorkingHoursDTO;
use App\DTO\WorkingHours\WeeklyWorkingHoursDTO;
use App\Entity\Schedule;
use App\Response\SuccessResponse;
use App\Service\Auth\AccessRule\ScheduleWritePrivilegesRule;
use App\Service\Auth\Attribute\RestrictedAccess;
use App\Service\Entity\WorkingHoursService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[ServerErrorResponseDoc]
#[OA\Tag('WorkingHours')]
class WorkingHoursController extends AbstractController
{
    #[OA\Put(
        summary: 'Update schedule weekly working hours',
        description: 'Updates schedule weekly working hours.
        </br>**Important:** This action can only be performed by organization admin or schedule assignee with *WRITE* privileges.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Schedule weekly working hours have been updated'])]
    #[NotFoundResponseDoc('Schedule not found')]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ScheduleWritePrivilegesRule::class)]
    #[Route('schedules/{schedule}/weekly-working-hours', name: 'schedule_weekly_working_hours_update', methods: ['PUT'], requirements: ['schedule' => '\d+'])]
    public function updateWeeklyWorkingHours(
        Schedule $schedule,
        #[MapRequestPayload] WeeklyWorkingHoursDTO $dto,
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
    #[WeeklyWorkingHoursResponseDoc]
    #[NotFoundResponseDoc('Schedule not found')]
    #[Route('schedules/{schedule}/weekly-working-hours', name: 'schedule_weekly_working_hours_get', methods: ['GET'], requirements: ['schedule' => '\d+'])]
    public function get(Schedule $schedule, EntitySerializerInterface $entitySerializer): SuccessResponse
    {
        $responseData = $entitySerializer->normalize($schedule->getWeekdayTimeWindows(), []);

        return new SuccessResponse($responseData);
    }

    #[OA\Put(
        summary: 'Update custom schedule working hours',
        description: 'Updates custom schedule working hours for specific date.
        </br>**Important:** This action can only be performed by organization admin or schedule assignee with *WRITE* privileges.'
    )]
    #[SuccessResponseDoc(dataExample: ['message' => 'Schedule custom working hours have been updated'])]
    #[NotFoundResponseDoc('Schedule not found')]
    #[ValidationErrorResponseDoc]
    #[ForbiddenResponseDoc]
    #[UnauthorizedResponseDoc]
    #[RestrictedAccess(ScheduleWritePrivilegesRule::class)]
    #[Route('schedules/{schedule}/custom-working-hours', name: 'schedule_custom_working_hours_update', methods: ['PUT'], requirements: ['schedule' => '\d+'])]
    public function updateCustomWorkingHours(
        Schedule $schedule,
        #[MapRequestPayload] CustomWorkingHoursDTO $dto,
        WorkingHoursService $workingHoursService,
    ): SuccessResponse
    {
        $workingHoursService->setScheduleCustomWorkingHours($schedule, $dto);

        return new SuccessResponse(['message' => 'Schedule custom working hours have been updated']);
    }
}