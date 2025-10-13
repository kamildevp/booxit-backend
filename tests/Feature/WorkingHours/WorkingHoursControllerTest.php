<?php

declare(strict_types=1);

namespace App\Tests\Feature\WorkingHours;

use App\DataFixtures\Test\ScheduleAssignment\ScheduleAssignmentFixtures;
use App\DataFixtures\Test\WorkingHours\CustomWorkingHoursFixtures;
use App\DataFixtures\Test\WorkingHours\WeeklyWorkingHoursFixtures;
use App\Repository\ScheduleRepository;
use App\Repository\UserRepository;
use App\Tests\Feature\WorkingHours\DataProvider\GetCustomWorkingHoursDataProvider;
use App\Tests\Feature\WorkingHours\DataProvider\GetWeeklyWorkingHoursDataProvider;
use App\Tests\Feature\WorkingHours\DataProvider\UpdateCustomWorkingHoursDataProvider;
use App\Tests\Utils\Attribute\Fixtures;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Utils\BaseWebTestCase;
use App\Tests\Feature\WorkingHours\DataProvider\UpdateWeeklyWorkingHoursDataProvider;

class WorkingHoursControllerTest extends BaseWebTestCase
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
    #[DataProviderExternal(UpdateWeeklyWorkingHoursDataProvider::class, 'validDataCases')]
    public function testUpdateScheduleWeeklyWorkingHours(array $params): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'PUT', "/api/schedules/$scheduleId/weekly-working-hours", $params);
        $this->assertEquals(['message' => 'Schedule weekly working hours have been updated'], $responseData);
    }

    #[Fixtures([ScheduleAssignmentFixtures::class])]
    #[DataProviderExternal(UpdateWeeklyWorkingHoursDataProvider::class, 'validationDataCases')]
    public function testUpdateScheduleWeeklyWorkingHoursValidation(array $params, array $expectedErrors): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'PUT', "/api/schedules/$scheduleId/weekly-working-hours", $params, $expectedErrors);
    }

    #[Fixtures([WeeklyWorkingHoursFixtures::class])]
    #[DataProviderExternal(GetWeeklyWorkingHoursDataProvider::class, 'dataCases')]
    public function testGetScheduleWeeklyWorkingHours(array $expectedResponseData): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', "/api/schedules/$scheduleId/weekly-working-hours");
        $this->assertEquals($expectedResponseData, $responseData);
    }

    #[Fixtures([ScheduleAssignmentFixtures::class])]
    #[DataProviderExternal(UpdateCustomWorkingHoursDataProvider::class, 'validDataCases')]
    public function testUpdateScheduleCustomWorkingHours(array $params): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'PUT', "/api/schedules/$scheduleId/custom-working-hours", $params);
        $this->assertEquals(['message' => 'Schedule custom working hours have been updated'], $responseData);
    }

    #[Fixtures([ScheduleAssignmentFixtures::class])]
    #[DataProviderExternal(UpdateCustomWorkingHoursDataProvider::class, 'validationDataCases')]
    public function testUpdateScheduleCustomWorkingHoursValidation(array $params, array $expectedErrors): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'PUT', "/api/schedules/$scheduleId/custom-working-hours", $params, $expectedErrors);
    }

    #[Fixtures([CustomWorkingHoursFixtures::class])]
    #[DataProviderExternal(GetCustomWorkingHoursDataProvider::class, 'dataCases')]
    public function testScheduleGetCustomWorkingHours(array $query, array $expectedResponseData): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $path = '/api/schedules/'.$schedule->getId().'/custom-working-hours?' . http_build_query($query);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertEquals($expectedResponseData, $responseData);
    }

    #[Fixtures([CustomWorkingHoursFixtures::class])]
    #[DataProviderExternal(GetCustomWorkingHoursDataProvider::class, 'validationDataCases')]
    public function testGetScheduleCustomWorkingHoursValidation(array $query, array $expectedErrors): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $path = '/api/schedules/'.$schedule->getId().'/custom-working-hours?' . http_build_query($query);

        $this->assertPathValidation($this->client, 'GET', $path, [], $expectedErrors);
    }

    #[Fixtures([CustomWorkingHoursFixtures::class])]
    #[DataProviderExternal(GetCustomWorkingHoursDataProvider::class, 'removeDataCases')]
    public function testRemoveScheduleCustomWorkingHours(string $date): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $schedule = $this->scheduleRepository->findOneBy([]);
        $path = '/api/schedules/'.$schedule->getId()."/custom-working-hours/$date";
        
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'DELETE', $path);

        $this->assertEquals(['message' => 'Custom working hours for specified date have been removed'], $responseData);
    }
}
