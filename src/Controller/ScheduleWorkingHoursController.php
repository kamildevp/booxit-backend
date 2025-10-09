<?php

declare(strict_types=1);

namespace App\Controller;

use App\Documentation\Response\ForbiddenResponseDoc;
use App\Documentation\Response\NotFoundResponseDoc;
use App\Documentation\Response\ServerErrorResponseDoc;
use App\Documentation\Response\SuccessResponseDoc;
use App\Documentation\Response\UnauthorizedResponseDoc;
use App\Documentation\Response\ValidationErrorResponseDoc;
use App\DTO\WorkingHours\WeeklyWorkingHoursDTO;
use App\Entity\Schedule;
use App\Response\SuccessResponse;
use App\Service\Auth\AccessRule\ScheduleWritePrivilegesRule;
use App\Service\Auth\Attribute\RestrictedAccess;
use App\Service\Entity\ScheduleWorkingHoursService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[ServerErrorResponseDoc]
#[OA\Tag('ScheduleWorkingHours')]
class ScheduleWorkingHoursController extends AbstractController
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
        ScheduleWorkingHoursService $scheduleWorkingHoursService,
    ): SuccessResponse
    {
        $scheduleWorkingHoursService->setScheduleWeeklyWorkingHours($schedule, $dto);

        return new SuccessResponse(['message' => 'Schedule weekly working hours have been updated']);
    }
}