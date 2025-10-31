<?php

declare(strict_types=1);

namespace App\Tests\Utils\Trait;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait ValidationAssertions
{
    protected function assertPathValidation(KernelBrowser $client, string $method, string $path, array $params, array $expectedErrors): void
    {
        $this->jsonRequest($client, $method, $path, $params);
        $decodedResponse = $this->getJsonResponse($this->client);
        $this->assertValidationErrorResponse($decodedResponse);
        $responseErrors = $decodedResponse['data']['errors'];
        $this->assertEquals($expectedErrors, $responseErrors);
    }
}