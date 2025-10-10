<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleWorkingHours;

use App\DataFixtures\Test\ScheduleAssignment\ScheduleAssignmentFixtures;
use App\DataFixtures\Test\ScheduleWorkingHours\ScheduleWeeklyWorkingHoursFixtures;
use App\Repository\ScheduleRepository;
use App\Repository\UserRepository;
use App\Tests\Feature\ScheduleWorkingHours\DataProvider\ScheduleGetWeeklyWorkingHoursDataProvider;
use App\Tests\Feature\ScheduleWorkingHours\DataProvider\ScheduleUpdateDateWorkingHoursDataProvider;
use App\Tests\Utils\Attribute\Fixtures;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Utils\BaseWebTestCase;
use App\Tests\Feature\ScheduleWorkingHours\DataProvider\ScheduleUpdateWeeklyWorkingHoursDataProvider;

class ScheduleWorkingHoursControllerTest extends BaseWebTestCase
{
    protected ScheduleRepository $scheduleRepository;
    protected UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scheduleRepository = $this->container->get(ScheduleRepository::class);
        $this->userRepository = $this->container->get(UserRepository::class);
    }

    #[Fixtures([ScheduleAssignmentFixtures::class])]
    #[DataProviderExternal(ScheduleUpdateWeeklyWorkingHoursDataProvider::class, 'validDataCases')]
    public function testUpdateWeeklyWorkingHours(array $params): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'PUT', "/api/schedules/$scheduleId/weekly-working-hours", $params);
        $this->assertEquals(['message' => 'Schedule weekly working hours have been updated'], $responseData);
    }

    #[Fixtures([ScheduleAssignmentFixtures::class])]
    #[DataProviderExternal(ScheduleUpdateWeeklyWorkingHoursDataProvider::class, 'validationDataCases')]
    public function testUpdateWeeklyWorkingHoursValidation(array $params, array $expectedErrors): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'PUT', "/api/schedules/$scheduleId/weekly-working-hours", $params, $expectedErrors);
    }

    #[Fixtures([ScheduleWeeklyWorkingHoursFixtures::class])]
    #[DataProviderExternal(ScheduleGetWeeklyWorkingHoursDataProvider::class, 'dataCases')]
    public function testGetWeeklyWorkingHours(array $expectedResponseData): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', "/api/schedules/$scheduleId/weekly-working-hours");
        $this->assertEquals($expectedResponseData, $responseData);
    }

    #[Fixtures([ScheduleAssignmentFixtures::class])]
    #[DataProviderExternal(ScheduleUpdateDateWorkingHoursDataProvider::class, 'validDataCases')]
    public function testUpdateDateWorkingHours(array $params): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'PUT', "/api/schedules/$scheduleId/date-working-hours", $params);
        $this->assertEquals(['message' => 'Schedule date working hours have been updated'], $responseData);
    }

    #[Fixtures([ScheduleAssignmentFixtures::class])]
    #[DataProviderExternal(ScheduleUpdateDateWorkingHoursDataProvider::class, 'validationDataCases')]
    public function testUpdateDateWorkingHoursValidation(array $params, array $expectedErrors): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'PUT', "/api/schedules/$scheduleId/date-working-hours", $params, $expectedErrors);
    }
}
