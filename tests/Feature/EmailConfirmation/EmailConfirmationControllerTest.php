<?php

declare(strict_types=1);

namespace App\Tests\Feature\EmailConfirmation;

use App\DataFixtures\Test\User\PasswordResetFixtures;
use App\DataFixtures\Test\User\VerifyUserEmailFixtures;
use App\DataFixtures\Test\User\VerifyUserFixtures;
use App\Enum\EmailConfirmationType;
use App\Tests\Feature\EmailConfirmation\DataProvider\EmailConfirmationValidateDataProvider;
use App\Tests\Utils\Attribute\Fixtures;
use App\Tests\Utils\BaseWebTestCase;
use App\Tests\Utils\Trait\EmailConfirmationUtils;
use PHPUnit\Framework\Attributes\DataProviderExternal;

class EmailConfirmationControllerTest extends BaseWebTestCase
{
    use EmailConfirmationUtils;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Fixtures([VerifyUserFixtures::class, VerifyUserEmailFixtures::class, PasswordResetFixtures::class])]
    #[DataProviderExternal(EmailConfirmationValidateDataProvider::class, 'validDataCases')]
    public function testValidateSuccess(EmailConfirmationType $type): void
    {
        $params = $this->prepareEmailConfirmationVerifyParams($type);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', '/api/email-confirmations/validate', $params);

        $this->assertEquals(['valid' => true], $responseData);
    }

    #[Fixtures([VerifyUserFixtures::class, VerifyUserEmailFixtures::class, PasswordResetFixtures::class])]
    #[DataProviderExternal(EmailConfirmationValidateDataProvider::class, 'failureDataCases')]
    public function testValidateFailure(array $verifyParams, EmailConfirmationType $type): void
    {
        $validParams = $this->prepareEmailConfirmationVerifyParams($type);
        $params = array_merge($validParams, $verifyParams);
        $responseData = $this->getFailureResponseData($this->client, 'GET', '/api/email-confirmations/validate', $params);

        $this->assertEquals('Validation Failed', $responseData['message']);
    }

    #[DataProviderExternal(EmailConfirmationValidateDataProvider::class, 'validationDataCases')]
    public function testValidateValidation(array $params, array $expectedErrors): void
    {
        $this->assertPathValidation($this->client, 'GET', '/api/email-confirmations/validate', $params, $expectedErrors);
    }
}
